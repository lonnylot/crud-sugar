<?php

namespace CrudSugar\Concerns;

use ReflectionClass;
use Exception;

trait HasEndpoints {

  protected $endpoints = [];

  public function registerEndpointClass(string $endpointClass) {
    $reflect = new ReflectionClass($endpointClass);
    if (!in_array(IsEndpoint::class, array_keys($reflect->getTraits()))) {
      throw new Exception($endpointClass." must use ".IsEndpoint::class);
    }

    // Convert a class name 'DummyEndpoint' to 'dummyEndpoint'
    $endpointName = lcfirst($reflect->getShortName());

    if (isset($this->endpoints[$endpointName])) {
      throw new Exception($reflect->getShortName()." has already been registered.");
    }

    $this->endpoints[$endpointName] = new $endpointClass();
    $this->endpoints[$endpointName]->setClient($this);
    $this->endpoints[$endpointName]->buildResourcePaths();

    return $this->endpoints[$endpointName];
  }

  public function __get($name) {
    if (!isset($this->endpoints[$name])) {
      throw new Exception("The '" . $name . "' endpoint was not found. Please register it with ".self::class."::registerEndpointClass()");
    }

    return $this->endpoints[$name];
  }
}
