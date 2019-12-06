<?php

namespace CrudSugar\Concerns;

trait HasStaticInstances {
  protected static $instances = [];

  protected $name = null;

  public static function getInstance($name = null) {
    if (is_null($name)) {
      $name = uniqid();
    }

    if (!isset(self::$instances[$name])) {
      self::$instances[$name] = new static();
    }

    self::$instances[$name]->setName($name);

    return self::$instances[$name];
  }

  private function setName($name) {
    $this->name = $name;
  }

  public function getName() {
    return $this->name;
  }

  public function reset() {
    $name = $this->name;
    unset(self::$instances[$name]);
    return self::getInstance($name);
  }
}
