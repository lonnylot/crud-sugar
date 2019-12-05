<?php

namespace CrudSugar\Concerns;

use CrudSugar\Client;

interface Endpoint {
  public function __construct(Client $client);
}
