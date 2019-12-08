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

class Client {

  use HasStaticInstances;
  use HasEndpoints;

  protected $baseUrl = '';

  protected $key = null;

  protected $handler = null;

  protected $requestStats = [];

  protected $validator = null;

  public function setValidatorFactory(Factory $validator) {
    $this->validator = $validator;
  }

  public function getValidatorFactory() {
    if (!is_null($this->validator)) {
      return $this->validator;
    }

    // TODO: In the future we can set this up to actually handle translations
    $translator = new Translator(new FileLoader(new Filesystem, ''), 'en');
    $this->validator = new Factory($translator);

    return $this->validator;
  }

  public function setApiKey($key) {
    if (!is_string($key)) {
      throw new Exception('Key must be a string.');
    }

    $this->key = $key;
  }

  public function setBaseUrl($baseUrl) {
    if (!is_string($baseUrl)) {
      throw new Exception('Base URL must be a string.');
    }

    $this->baseUrl = $baseUrl;
  }

  public function setHandler(callable $handler) {
    $this->handler = $handler;
  }

  public function getHandler() {
    if (is_null($this->handler)) {
      throw new Exception('Handler must be set via '.self::class.'::setHandler()');
    }

    return $this->handler;
  }

  public function getApiKey() {
    if (is_null($this->key)) {
      throw new Exception('Key must be set via '.self::class.'::setKey()');
    }

    return $this->key;
  }

  public function getBaseUrl() {
    return $this->baseUrl;
  }

  public function request($method, $path, $data = null) {
    [$path, $data] = $this->bindPathParams($path, $data);

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
        if (is_array($data)) {
          $data = json_encode($data);
        }
      }
    }

    $clientOptions = [
      'base_uri' => $this->getBaseUrl(),
    ];

    if (!is_null($this->handler)) {
      $clientOptions['handler'] = $this->handler;
    }

    $guzzleClient = new GuzzleClient($clientOptions);

    try {
      $guzzleResponse = $guzzleClient->request($method, $uri, [
        'headers' => [
          'Authorization' => 'Bearer '.$this->getApiKey(),
          'Accept' => 'application/json',
          'Content-Type' => 'application/json',
          'User-Agent' => 'crud-sugar-sdk/1.0'
        ],
        'timeout' => 10,
        'body' => $data,
        'on_stats' => [$this, 'recordStats']
      ]);

      return new Response($guzzleResponse);
    } catch (RequestException $e) {
      return $this->generateResponseFromRequestException($e);
    }
  }

  public function generateResponseFromRequestException(RequestException $e) {
    return new Response($e->getResponse());
  }

  public function bindPathParams($path, $data) {
    $bindings = [];
    preg_match_all('/{[\\d\\w]*}/', $path, $bindings);

    if (count($bindings[0]) === 0 || !is_array($data)) {
      return [$path, $data];
    }

    foreach($bindings[0] as $binding) {
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

  public function recordStats(TransferStats $stats) {
    $this->requestStats[] = $stats;
  }

  public function getLatestRequestStats() {
    return end($this->requestStats);
  }
}
