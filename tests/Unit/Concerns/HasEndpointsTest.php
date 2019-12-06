<?php

namespace Tests\Unit\Concerns;

use Exception;
use Tests\TestCase;

use CrudSugar\Concerns\HasEndpoints;
use CrudSugar\Concerns\IsEndpoint;

class HasEndpointsTest extends TestCase {
  public function testUnsetEndpoint() {
    // Given
    $classWithTrait = $this->getObjectForTrait(HasEndpoints::class);
    $this->expectException(Exception::class);

    // When
    $classWithTrait->purchase->all();

    // Then
  }

  public function testRegisterStdClassAsEndpoint() {
    // Given
    $classWithTrait = $this->getObjectForTrait(HasEndpoints::class);
    $this->expectException(Exception::class);

    // When
    $classWithTrait->registerEndpointClass('stdClass');

    // Then
  }

  public function testRegisterEndpoint() {
    // Given
    $classWithTrait = $this->getObjectForTrait(HasEndpoints::class);
    $endpointClass = get_class($this->getObjectForTrait(IsEndpoint::class));

    // When
    $endpoint = $classWithTrait->registerEndpointClass($endpointClass);

    // Then
    $this->assertTrue($endpoint instanceof $endpointClass);
  }

  public function testUserRegisteredEndpointIsCallable() {
    // Given
    $classWithTrait = $this->getObjectForTrait(HasEndpoints::class);
    $endpointClass = get_class($this->getObjectForTrait(IsEndpoint::class));
    $endpointName = lcfirst($endpointClass);
    $classWithTrait->registerEndpointClass($endpointClass);

    // When
    $result = $classWithTrait->$endpointName->setClient(uniqid());

    // Then
    $this->assertNull($result);
  }

  public function testRegisterDuplicateEndpoint() {
    // Given
    $classWithTrait = $this->getObjectForTrait(HasEndpoints::class);
    $endpointClass = get_class($this->getObjectForTrait(IsEndpoint::class));
    $classWithTrait->registerEndpointClass($endpointClass);
    $this->expectException(Exception::class);

    // When
    $classWithTrait->registerEndpointClass($endpointClass);

    // Then
  }
}
