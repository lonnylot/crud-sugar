<?php

namespace Tests\Unit\Concerns;

use Exception;
use Tests\TestCase;

use CrudSugar\Concerns\IsEndpoint;
use CrudSugar\Response;

class IsEndpointsTest extends TestCase {
  public function testNeedsAllResourcePaths() {
    // Given
    $classWithTrait = $this->getObjectForTrait(IsEndpoint::class);

    // When

    // Then
    $this->assertTrue($classWithTrait->needsResourcePath('index'));
    $this->assertTrue($classWithTrait->needsResourcePath('show'));
    $this->assertTrue($classWithTrait->needsResourcePath('update'));
    $this->assertTrue($classWithTrait->needsResourcePath('store'));
    $this->assertTrue($classWithTrait->needsResourcePath('delete'));
  }

  public function testAllResourcePathsAreBuilt() {
    // Given
    $classWithTrait = $this->getObjectForTrait(IsEndpoint::class);

    // When
    $classWithTrait->buildResourcePaths();

    // Then
    $this->assertFalse($classWithTrait->needsResourcePath('index'));
    $this->assertFalse($classWithTrait->needsResourcePath('show'));
    $this->assertFalse($classWithTrait->needsResourcePath('update'));
    $this->assertFalse($classWithTrait->needsResourcePath('store'));
    $this->assertFalse($classWithTrait->needsResourcePath('delete'));
  }

  public function testCallAllowedResource() {
    // Given
    $classWithTrait = $this->getObjectForTrait(IsEndpoint::class);
    $this->getClient()->setApiKey(uniqid());
    $classWithTrait->setClient($this->getClient());
    $classWithTrait->buildResourcePaths();

    // When
    $response = $classWithTrait->index();

    // Then
    $this->assertInstanceOf(Response::class, $response);
  }

  public function testCallUnallowedResource() {
    // Given
    $classWithTrait = $this->getObjectForTrait(IsEndpoint::class);

    $this->expectException(Exception::class);

    // When
    $response = $classWithTrait->all();

    // Then
  }
}
