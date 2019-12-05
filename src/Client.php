<?php

namespace CrudSugar;

use CrudSugar\Concerns\Endpoint;
use Exception;
use ReflectionClass;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\TransferStats;

class Client {
  protected $baseUrl = 'https://api.clarityboard.com/v/';

  protected $key = null;

  protected $handler = null;

  protected $requestStats = [];

  protected static $instances = [];

  protected $name = null;

  protected $endpoints = [];

  protected function __construct($name) {
    $this->name = $name;
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

  public function request($method, $endpoint, $data = null) {
    $uri = new Uri($endpoint);

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

    $client = new GuzzleClient($clientOptions);

    return $client->request($method, $uri, [
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
  }

  public function recordStats(TransferStats $stats) {
    $this->requestStats[] = $stats;
  }

  public function getLatestRequestStats() {
    return end($this->requestStats);
  }

  public static function getInstance($name = null) {
    if (is_null($name)) {
      $name = uniqid();
    }

    if (!isset(self::$instances[$name])) {
      self::$instances[$name] = new self($name);
    }

    return self::$instances[$name];
  }

  public function reset() {
    $this->requestStats = [];
    $this->handler = null;
    $this->endpoints = [];
  }

  public function registerEndpointClass(string $endpointClass) {
    $reflect = new ReflectionClass($endpointClass);
    if (!in_array(Endpoint::class, array_keys($reflect->getInterfaces()))) {
      throw new Exception($endpointClass." must implement ".Endpoint::class);
    }

    // Convert a class name 'DummyEndpoint' to 'dummyEndpoint'
    $endpointName = lcfirst($reflect->getShortName());

    if (isset($this->endpoints[$endpointName])) {
      throw new Exception($reflect->getShortName()." has already been registered.");
    }

    $this->endpoints[$endpointName] = new $endpointClass($this);

    return $this->endpoints[$endpointName];
  }

  public function __get($name) {
    if (!isset($this->endpoints[$name])) {
      throw new Exception("The '" . $name . "' endpoint was not found. Please register it with ".self::class."::registerEndpointClass()");
    }

    return $this->endpoints[$name];
  }
}
