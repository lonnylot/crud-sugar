<?php

namespace CrudSugar\Concerns;

use ReflectionClass;
use Exception;

trait IsEndpoint {
  private $client;
  
  public function setClient($client) {
    $this->client = $client;
  }
}
