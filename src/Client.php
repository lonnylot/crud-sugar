<?php

namespace CrudSugar;

use CrudSugar\Concerns\HasStaticInstances;
use CrudSugar\Concerns\HasEndpoints;
use Exception;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\TransferStats;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Translation\FileLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;

require_once('version.php');

class Client
{
    use HasStaticInstances;
    use HasEndpoints;

    protected $baseUrl = '';

    protected $key = null;

    protected $handler = null;

    protected $customClientOptions = null;

    protected $requestStats = [];

    protected $validator = null;

    protected $authHeaders = null;

    protected $contentType = 'application/json';

    protected $verifySsl = true;

    protected $timeout = 10;

    protected $uriAndDataParamsResolver = null;

    protected $dataParamModifier = null;

    public function setValidatorFactory(Factory $validator)
    {
        $this->validator = $validator;
    }

    public function getValidatorFactory()
    {
        if (!is_null($this->validator)) {
            return $this->validator;
        }

        // TODO: In the future we can set this up to actually handle translations
        $translator = new Translator(new FileLoader(new Filesystem, ''), 'en');
        $this->validator = new Factory($translator);

        return $this->validator;
    }

    public function setApiKey($key)
    {
        if (!is_string($key)) {
            throw new Exception('Key must be a string.');
        }

        $this->key = $key;
    }

    public function setBaseUrl($baseUrl)
    {
        if (!is_string($baseUrl)) {
            throw new Exception('Base URL must be a string.');
        }

        $this->baseUrl = $baseUrl;
    }

    public function setHandler(callable $handler)
    {
        $this->handler = $handler;
    }

    public function getHandler()
    {
        if (is_null($this->handler)) {
            throw new Exception('Handler must be set via '.self::class.'::setHandler()');
        }

        return $this->handler;
    }

    public function setClientOptions(array $clientOptions)
    {
        $this->customClientOptions = $clientOptions;
    }

    public function getApiKey()
    {
        if (is_null($this->key)) {
            throw new Exception('Key must be set via '.self::class.'::setKey()');
        }

        return $this->key;
    }

    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    public function request($method, $path, $data = null)
    {
        [$path, $data] = $this->bindPathParams($path, $data);

        [$uri, $resolvedDataParams] = $this->resolveUriAndDataParams($method, $path, $data);

        $clientOptions = [
          'base_uri' => $this->getBaseUrl(),
          'verify' => $this->getVerifySsl(),
        ];

        if (!is_null($this->handler)) {
            $clientOptions['handler'] = $this->handler;
        }

        if (!is_null($this->customClientOptions)) {
            $clientOptions = array_merge($clientOptions, $this->customClientOptions);
        }

        $headers = array_merge([
          'Accept' => 'application/json',
          'Content-Type' => $this->getContentTypeRequestValue(),
          'User-Agent' => $this->getUserAgent(),
        ], $this->getAuthHeaders($method, $path, $data));

        $guzzleClient = new GuzzleClient($clientOptions);

        try {
            $requestData = [
              'headers' => $headers,
              'timeout' => $this->getTimeout(),
              'on_stats' => [$this, 'recordStats'],
            ];

            if ($this->getContentTypeRequestValue() === 'application/x-www-form-urlencoded') {
                $requestData['form_params'] = $resolvedDataParams;
            } elseif ($this->getContentTypeRequestValue() === 'multipart/form-data') {
                $requestData['multipart'] = $resolvedDataParams;

                /**
                 * Guzzle needs to generate the Content-Type header so it includes a
                 * generated 'boundary' field.
                 * 
                 * Unset the Content-Type header hear and guzzle will automatically
                 * use multipart/form-data becase the 'multipart' key is set in the
                 * $requestData.
                 */
                unset($requestData['headers']['Content-Type']);
            } else {
                $requestData['body'] = $resolvedDataParams;
            }
            $guzzleResponse = $guzzleClient->request($method, $uri, $requestData);

            return new Response($guzzleResponse);
        } catch (RequestException $e) {
            return $this->generateResponseFromRequestException($e);
        }
    }

    public function getAuthHeaders($method, $path, $data)
    {
        if (is_array($this->authHeaders)) {
            return $this->authHeaders;
        }

        if (is_callable($this->authHeaders)) {
          return call_user_func_array($this->authHeaders, [$this, $method, $path, $data]);
        }

        return ['Authorization' => 'Bearer '.$this->getApiKey()];
    }

    public function setAuthHeaders($headers)
    {
        $this->authHeaders = $headers;
    }

    public function getContentTypeRequestValue()
    {
        return $this->contentType;
    }

    public function setContentTypeRequestValue($contentType)
    {
        $this->contentType = $contentType;
    }

    public function getVerifySsl()
    {
        return $this->verifySsl;
    }

    public function setVerifySsl(bool $verifySsl)
    {
        $this->verifySsl = $verifySsl;
    }

    public function getTimeout()
    {
        return $this->timeout;
    }

    public function setTimeout(int $timeout)
    {
        $this->timeout = $timeout;
    }

    public function getUserAgent(): string
    {
        return 'crud-sugar-sdk/'.CRUD_SUGAR_VERSION;
    }

    public function generateResponseFromRequestException(RequestException $e)
    {
        return new Response($e->getResponse(), $e);
    }

    public function bindPathParams($path, $data)
    {
        $bindings = [];
        preg_match_all('/{[\\d\\w]*}/', $path, $bindings);

        if (count($bindings[0]) === 0 || !is_array($data)) {
            return [$path, $data];
        }

        foreach ($bindings[0] as $binding) {
            $paramName = str_replace('{', '', str_replace('}', '', $binding));

            if (isset($data[$paramName])) {
                $path = str_replace($binding, $data[$paramName], $path);

                unset($data[$paramName]);
            }
        }

        if (count($data) === 0) {
            $data = null;
        }

        return [$path, $data];
    }

    public function resolveUriAndDataParams($method, $path, $data)
    {
        if ($this->uriAndDataParamsResolver) {
          return call_user_func_array($this->uriAndDataParamsResolver, [$method, $path, $data]);
        }

        if ($this->dataParamModifier) {
            $data = call_user_func_array($this->dataParamModifier, [$data]);
        }

        $uri = new Uri($path);

        switch ($method) {
          case 'GET':
          case 'HEAD': {
            if (is_array($data)) {
                $query = http_build_query($data);
                $query = preg_replace('/%5B[0-9]+%5D/simU', '%5B%5D', $query); // NOTE: This replaces numeric indexes in arrays
                $uri = $uri->withQuery($query);
                $data = null;
            }
            break;
          }
          default: {
            if (is_array($data) && $this->getContentTypeRequestValue() === 'application/json') {
                $data = json_encode($data);
            }
          }
        }

        return [$uri, $data];
    }

    public function setUriAndDataParamsResolver(callable $callable)
    {
      $this->uriAndDataParamsResolver = $callable;
    }

    public function setDataParamModifier(callable $callable)
    {
        $this->dataParamModifier = $callable;
    }

    public function recordStats(TransferStats $stats)
    {
        $this->requestStats[] = $stats;
    }

    public function getLatestRequestStats()
    {
        return end($this->requestStats);
    }
}
