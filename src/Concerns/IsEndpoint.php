<?php

namespace CrudSugar\Concerns;

use Exception;

trait IsEndpoint {
  private $client;

  private $path = '';

  private $resources = [
    'index',
    'store',
    'show',
    'update',
    'delete',
  ];

  private $resourceKey = 'id';

  private $resourcePaths = [];

  private $resourceMethods = [
    'index' => 'GET',
    'store' => 'POST',
    'show' => 'GET',
    'update' => 'PUT',
    'delete' => 'DELETE'
  ];

  public function setClient($client) {
    $this->client = $client;
  }

  public function buildResourcePaths() {
    if ($this->needsResourcePath('index')) {
      $this->resourcePaths['index'] = $this->path;
    }

    if ($this->needsResourcePath('store')) {
      $this->resourcePaths['store'] = $this->path;
    }

    if ($this->needsResourcePath('show')) {
      $this->resourcePaths['show'] = $this->path.'/{'.$this->resourceKey.'}';
    }

    if ($this->needsResourcePath('update')) {
      $this->resourcePaths['update'] = $this->path.'/{'.$this->resourceKey.'}';
    }

    if ($this->needsResourcePath('delete')) {
      $this->resourcePaths['delete'] = $this->path.'/{'.$this->resourceKey.'}';
    }
  }

  public function needsResourcePath($resource): bool {
    return in_array($resource, $this->resources) && !isset($this->resourcePaths[$resource]);
  }

  public function requestResource($resource, $params) {
    $this->validateResource($resource, $params);

    return $this->client->request($this->getResourceMethod($resource), $this->getResourcePath($resource), $params);
  }

  public function validateResource($resource, $params) {
    if (is_null($this->getResourceMethod($resource))) {
      throw new Exception('No method registered for "'.$resource.'".');
    }

    if (is_null($this->getResourcePath($resource))) {
      throw new Exception('No path registered for "'.$resource.'".');
    }

    $validateRulesMethodName = 'validate'.ucfirst(strtolower($resource)).'Rules';
    if (method_exists($this, $validateRulesMethodName)) {
      $validateRules = $this->$validateRulesMethodName();
      $this->client->getValidatorFactory()->validate($params ?? [], $validateRules);
    }
  }

  public function setPath(string $path) {
    $this->path = $path;
    $this->resourcePaths = [];
    $this->buildResourcePaths();
  }

  public function getPath(): string {
    return $this->path;
  }

  public function setResources(array $resources) {
    $this->resources = $resources;
  }

  public function getResources(): array {
    return $this->resources;
  }

  public function setResourcePath(string $resource, string $path) {
    $this->resourcePaths[$resource] = $path;
  }

  public function getResourcePath(string $resource) {
    return $this->resourcePaths[$resource] ?? null;
  }

  public function setResourceMethod(string $resource, string $method) {
    $this->resourceMethods[$resource] = $method;
  }

  public function getResourceMethod(string $resource) {
    return $this->resourceMethods[$resource] ?? null;
  }

  public function __call($name, $arguments) {
    if (in_array($name, $this->resources)) {
      return $this->requestResource($name, count($arguments) ? $arguments[0] : null);
    }

    throw new Exception('The resource named "'.$name.'" is unavailable for this endpoint');
  }
}
