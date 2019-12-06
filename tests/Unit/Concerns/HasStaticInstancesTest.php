<?php

namespace Tests\Unit\Concerns;

use Exception;
use Tests\TestCase;

use CrudSugar\Concerns\HasStaticInstances;

class HasStaticInstancesTest extends TestCase {
  public function testCanGetSameInstance() {
    // Given
    $classWithTrait = $this->getObjectForTrait(HasStaticInstances::class);

    // When
    $instance = $classWithTrait::getInstance();

    // Then
    $this->assertSame($instance, $classWithTrait::getInstance($instance->getName()));
  }

  public function testInstancesAreDifferent() {
    // Given
    $classWithTrait = $this->getObjectForTrait(HasStaticInstances::class);

    // When
    $instance0 = $classWithTrait::getInstance();
    $instance1 = $classWithTrait::getInstance();

    // Then
    $this->assertNotSame($instance0, $instance1);
  }

  public function testCanNameInstance() {
    // Given
    $classWithTrait = $this->getObjectForTrait(HasStaticInstances::class);
    $name = uniqid();

    // When
    $instance = $classWithTrait::getInstance($name);

    // Then
    $this->assertSame($name, $instance->getName());
  }

  public function testResetClearsAttributes() {
    // Given
    $originalName = $this->getClient()->getName();
    $this->getClient()->setBaseUrl(uniqid());

    // When
    $this->getClient()->reset();
    $nameAfterReset = $this->getClient()->getName();

    // Then
    $this->assertSame($originalName, $nameAfterReset);
    $this->assertEmpty($this->getClient()->getBaseUrl());
  }
}
