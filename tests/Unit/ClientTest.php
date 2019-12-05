<?php

namespace Tests\Unit;

use Exception;
use Tests\TestCase;
use Tests\DummyEndpoint;

use CrudSugar\Client;

class ClientTest extends TestCase {
  public function testSetApiRequiresString() {
    // Given
    $this->expectException(Exception::class);

    // When
    $this->getClient()->setApiKey(1);

    // Then
    // Exception is thrown
  }

  public function testSetBaseUrlRequiresString() {
    // Given
    $this->expectException(Exception::class);

    // When
    $this->getClient()->setBaseUrl(1);

    // Then
    // Exception is thrown
  }

  public function testGetSetKey() {
    // Given
    $key = 'abc123';

    // When
    $this->getClient()->setApiKey($key);

    // Then
    $this->assertEquals($this->getClient()->getApiKey(), $key);
  }

  public function testGetSetBaseUrl() {
    // Given
    $baseUrl = 'https://fakeurl/';

    // When
    $this->getClient()->setBaseUrl($baseUrl);

    // Then
    $this->assertEquals($this->getClient()->getBaseUrl(), $baseUrl);
  }

  public function testUnsetEndpoint() {
    // Given
    $this->expectException(Exception::class);

    // When
    $this->getClient()->purchase->all();

    // Then
  }

  public function testRegisterStdClassAsEndpoint() {
    // Given
    $this->expectException(Exception::class);

    // When
    $this->getClient()->registerEndpointClass('stdClass');

    // Then
  }

  public function testRegisterEndpoint() {
    // Given
    $endpointClass = DummyEndpoint::class;

    // When
    $endpoint = $this->getClient()->registerEndpointClass($endpointClass);

    // Then
    $this->assertTrue($endpoint instanceof $endpointClass);
  }

  public function testUserRegisteredEndpoint() {
    // Given
    $endpointClass = DummyEndpoint::class;
    $this->getClient()->registerEndpointClass($endpointClass);

    // When
    $result = $this->getClient()->dummyEndpoint->all();

    // Then
    $this->assertTrue(is_array($result));
  }

  public function testRegisterDuplicateEndpoint() {
    // Given
    $endpointClass = DummyEndpoint::class;
    $this->getClient()->registerEndpointClass($endpointClass);
    $this->expectException(Exception::class);

    // When
    $this->getClient()->registerEndpointClass($endpointClass);

    // Then
  }
}
