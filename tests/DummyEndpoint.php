<?php

namespace Tests;

use CrudSugar\Concerns\Endpoint;
use CrudSugar\Client;

class DummyEndpoint implements Endpoint {
  protected $baseEndpoint = '/dummy';

  private $client;

  public function __construct(Client $client) {
    $this->client = $client;
  }

  public function all() {
    // $this->client->request('GET', $this->baseEndpoint);
    return [];
  }
}
