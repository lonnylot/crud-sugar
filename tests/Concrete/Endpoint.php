<?php

namespace Tests\Concrete;

use CrudSugar\Concerns\IsEndpoint;

class Endpoint {
  use IsEndpoint;

  public static $setPathTo = '';

  public function boot() {
    $this->setPath(self::$setPathTo);
    $this->setResources(array_merge($this->getResources(), ['nomethod', 'nopath', 'invalid']));
    $this->setResourceMethod('nopath', 'GET');
    $this->setResourceMethod('invalid', 'GET');
    $this->setResourcePath('invalid', uniqid());
  }

  public function validateInvalidRules() {
    return [
      uniqid() => ['required']
    ];
  }
}
