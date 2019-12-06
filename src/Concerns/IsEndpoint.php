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

  public function needsResourcePath($resource) {
    return in_array($resource, $this->resources) && !isset($this->resourcePaths[$resource]);
  }

  public function requestResource($resource, $params) {
    return $this->client->request($this->resourceMethods[$resource], $this->resourcePaths[$resource], $params);
  }

  public function __call($name, $arguments) {
    if (in_array($name, $this->resources)) {
      return $this->requestResource($name, count($arguments) ? $arguments[0] : null);
    }

    throw new Exception('The resource named "'.$name.'" is unavailable for this endpoint');
  }
}
