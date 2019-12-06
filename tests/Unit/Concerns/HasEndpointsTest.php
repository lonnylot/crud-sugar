<?php

namespace Tests\Unit\Concerns;

use Exception;
use Tests\TestCase;
use Tests\DummyEndpoint;

use CrudSugar\Concerns\HasEndpoints;

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
    $endpointClass = DummyEndpoint::class;

    // When
    $endpoint = $classWithTrait->registerEndpointClass($endpointClass);

    // Then
    $this->assertTrue($endpoint instanceof $endpointClass);
  }

  public function testUserRegisteredEndpoint() {
    // Given
    $classWithTrait = $this->getObjectForTrait(HasEndpoints::class);
    $endpointClass = DummyEndpoint::class;
    $classWithTrait->registerEndpointClass($endpointClass);

    // When
    $result = $classWithTrait->dummyEndpoint->all();

    // Then
    $this->assertTrue(is_array($result));
  }

  public function testRegisterDuplicateEndpoint() {
    // Given
    $classWithTrait = $this->getObjectForTrait(HasEndpoints::class);
    $endpointClass = DummyEndpoint::class;
    $classWithTrait->registerEndpointClass($endpointClass);
    $this->expectException(Exception::class);

    // When
    $classWithTrait->registerEndpointClass($endpointClass);

    // Then
  }
}
