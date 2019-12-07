<?php

namespace Tests\Concrete;

use CrudSugar\Concerns\IsEndpoint;

class Endpoint {
  use IsEndpoint;

  public static $setPathTo = '';

  public function boot() {
    $this->setPath(self::$setPathTo);
  }
}
