<?php

namespace Tests\Unit\Concerns;

use Tests\TestCase;
use Tests\Concrete\Endpoint;
use CrudSugar\Concerns\IsEndpoint;
use CrudSugar\Response;
use Exception;
use Illuminate\Validation\ValidationException;

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

  public function testCanSetPath() {
    // Given
    $classWithTrait = $this->getObjectForTrait(IsEndpoint::class);
    $path = uniqid();

    // When
    $classWithTrait->setPath($path);

    // Then
    $this->assertSame($path, $classWithTrait->getPath());
  }

  public function testCanSetResourcePath() {
    // Given
    $classWithTrait = $this->getObjectForTrait(IsEndpoint::class);
    $resource = uniqid();
    $path = uniqid();

    // When
    $classWithTrait->setResourcePath($resource, $path);

    // Then
    $this->assertSame($path, $classWithTrait->getResourcePath($resource));
  }

  public function testUndefinedResourcePathIsNull() {
    // Given
    $classWithTrait = $this->getObjectForTrait(IsEndpoint::class);
    $resource = uniqid();

    // When
    $resourcePath = $classWithTrait->getResourcePath($resource);

    // Then
    $this->assertNull($resourcePath);
  }

  public function testCanSetResourceMethod() {
    // Given
    $classWithTrait = $this->getObjectForTrait(IsEndpoint::class);
    $resource = uniqid();
    $method = uniqid();

    // When
    $classWithTrait->setResourceMethod($resource, $method);

    // Then
    $this->assertSame($method, $classWithTrait->getResourceMethod($resource));
  }

  public function testUndefinedResourceMethodIsNull() {
    // Given
    $classWithTrait = $this->getObjectForTrait(IsEndpoint::class);
    $resource = uniqid();

    // When
    $resourceMethod = $classWithTrait->getResourceMethod($resource);

    // Then
    $this->assertNull($resourceMethod);
  }

  public function testCanSetResources() {
    // Given
    $classWithTrait = $this->getObjectForTrait(IsEndpoint::class);
    $resources = [uniqid(), uniqid(), uniqid()];

    // When
    $classWithTrait->setResources($resources);

    // Then
    $this->assertSame($resources, $classWithTrait->getResources());
  }

  public function testDefaultResources() {
    // Given
    $classWithTrait = $this->getObjectForTrait(IsEndpoint::class);
    $defaultResources = [
      'index',
      'store',
      'show',
      'update',
      'delete',
    ];

    // When
    $endpointResources = $classWithTrait->getResources();

    // Then
    $this->assertSame($defaultResources, $endpointResources);
  }

  public function testValidateResourceWithNoResourceMethod() {
    // Given
    $this->expectException(Exception::class);

    // When
    $this->getClient()->registerEndpointClass(Endpoint::class);

    // Then
    $this->getClient()->endpoint->nomethod();
  }

  public function testValidateResourceWithNoResourcePath() {
    // Given
    $this->expectException(Exception::class);

    // When
    $this->getClient()->registerEndpointClass(Endpoint::class);

    // Then
    $this->getClient()->endpoint->nopath();
  }

  public function testValidateResourceWithValidationRulesFails() {
    // Given
    $this->expectException(ValidationException::class);

    // When
    $this->getClient()->registerEndpointClass(Endpoint::class);

    // Then
    $this->getClient()->endpoint->invalid();
  }
}
