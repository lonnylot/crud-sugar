<?php

namespace Tests\Unit;

use Exception;
use Tests\TestCase;

use CrudSugar\Client;
use CrudSugar\Response;

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

  public function testGetUnsetKey() {
    // Given
    $this->expectException(Exception::class);

    // When
    $this->getClient()->getApiKey();

    // Then
  }

  public function testGetSetBaseUrl() {
    // Given
    $baseUrl = 'https://fakeurl/';

    // When
    $this->getClient()->setBaseUrl($baseUrl);

    // Then
    $this->assertEquals($this->getClient()->getBaseUrl(), $baseUrl);
  }

  public function testRequestReturnsResponse() {
    // Given
    $endpoint = uniqid();
    $this->getClient()->setApiKey(uniqid());

    // When
    $response = $this->getClient()->request('GET', $endpoint);

    // Then
    $this->assertInstanceOf(Response::class, $response);
  }

  public function testPathParamsBinding() {
    // Given
    $originalData = [
      'id' => uniqid(),
      'barId' => uniqid()
    ];
    $path = 'foo/{id}/bar/{barId}';
    $finalPath = 'foo/'.$originalData['id'].'/bar/'.$originalData['barId'];

    // When
    [$path, $data] = $this->getClient()->bindPathParams($path, $originalData);

    // Then
    $this->assertEquals($finalPath, $path);
    $this->assertNull($data);
  }

  public function testPathParamsBindingReturnsUnboundData() {
    // Given
    $originalData = [
      'id' => uniqid(),
      'barId' => uniqid(),
      'someOtherData' => uniqid()
    ];
    $path = 'foo/{id}/bar/{barId}';
    $finalPath = 'foo/'.$originalData['id'].'/bar/'.$originalData['barId'];

    // When
    [$path, $data] = $this->getClient()->bindPathParams($path, $originalData);

    // Then
    $this->assertEquals($finalPath, $path);
    $this->assertCount(1, $data);
    $this->assertSame(['someOtherData' => $originalData['someOtherData']], $data);
  }
}
