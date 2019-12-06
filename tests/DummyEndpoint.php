<?php

namespace Tests;

use CrudSugar\Contracts\EndpointContract;

class DummyEndpoint implements EndpointContract {
  protected $baseEndpoint = '/dummy';

  private $client;

  public function __construct($client) {
    $this->client = $client;
  }

  public function all() {
    // $this->client->request('GET', $this->baseEndpoint);
    return [];
  }
}
