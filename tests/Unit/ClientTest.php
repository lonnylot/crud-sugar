<?php

namespace Tests\Unit;


use Tests\TestCase;
use CrudSugar\Client;
use CrudSugar\Response;
use Exception;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Translation\FileLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;

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

  public function testCanGenerateWorkableValidator() {
    // Given
    $validatorFactory = $this->getClient()->getValidatorFactory();
    $data = [
      'one' => uniqid(),
      'two' => [
        uniqid(),
        uniqid()
      ]
    ];
    $rules = [
      'one' => [
        'required', 'string'
      ],
      'two.*' => [
        'string'
      ]
    ];

    // When
    $validator = $validatorFactory->make($data, $rules);

    // Then
    $this->assertTrue($validator->passes());
  }

  public function testCanSetValidatorFactory() {
    // Given
    $translator = new Translator(new FileLoader(new Filesystem, ''), 'en');
    $validatorFactory = new Factory($translator);
    $validatorFactory->extend('isFive', function($attribute, $value) {
      return $value === 5;
    });
    $data = [
      'testField' => 5
    ];
    $rules = [
      'testField' => ['isFive']
    ];
    $this->getClient()->setValidatorFactory($validatorFactory);

    // When
    $validator = $this->getClient()->getValidatorFactory()
      ->make($data, $rules);

    // Then
    $this->assertTrue($validator->passes());
  }

  public function testUserAgent() {
    // Given
    $userAgent = $this->getClient()->getUserAgent();

    // When

    // Then
    $this->assertIsString($userAgent);
  }

  public function testGetDefaultAuthHeaders() {
    // Given
    $apiKey = uniqid();
    $this->getClient()->setApiKey($apiKey);

    // When
    $authHeaders = $this->getClient()->getAuthHeaders(uniqid(), uniqid(), uniqid());

    // Then
    $this->assertEquals(['Authorization' => 'Bearer '.$apiKey], $authHeaders);
  }

  public function testSetAuthHeaders() {
    // Given
    $authHeaderKey = uniqid();
    $apiKey = uniqid();
    $this->getClient()->setApiKey($apiKey);
    $this->getClient()->setAuthHeaders([$authHeaderKey => $apiKey]);

    // When
    $authHeaders = $this->getClient()->getAuthHeaders(uniqid(), uniqid(), uniqid());

    // Then
    $this->assertEquals([$authHeaderKey => $apiKey], $authHeaders);
  }

  public function testSetAuthHeadersWithClosure()
  {
    // Given
    $requestMethod = uniqid();
    $requestPath = uniqid();
    $requestData = [
      uniqid() => uniqid(),
    ];
    $responseAuth = uniqid();
    $callable = function($client, $method, $path, $data) use ($requestMethod, $requestPath, $requestData, $responseAuth) {
      $this->assertEquals($method, $requestMethod);
      $this->assertEquals($path, $requestPath);
      $this->assertEquals($data, $requestData);

      return $responseAuth;
    };
    $this->getClient()->setAuthHeaders($callable);

    // When
    $authHeaders = $this->getClient()->getAuthHeaders($requestMethod, $requestPath, $requestData);

    // Then
    $this->assertEquals($responseAuth, $authHeaders);
  }

  public function testGetContentTypeRequestValue() {
    // Given
    $apiKey = uniqid();
    $this->getClient()->setApiKey($apiKey);

    // When
    $contentType = $this->getClient()->getContentTypeRequestValue();

    // Then
    $this->assertEquals('application/json', $contentType);
  }

  public function testSetContentTypeRequestValue() {
    // Given
    $data = uniqid();
    $apiKey = uniqid();
    $this->getClient()->setApiKey($apiKey);
    $this->getClient()->setContentTypeRequestValue($data);

    // When
    $contentType = $this->getClient()->getContentTypeRequestValue();

    // Then
    $this->assertEquals($data, $contentType);
  }

  public function testVerifySsl() {
    // Given
    $apiKey = uniqid();
    $this->getClient()->setApiKey($apiKey);

    // When
    $r1 = $this->getClient()->getVerifySsl();
    $this->getClient()->setVerifySsl(false);
    $r2 = $this->getClient()->getVerifySsl();

    // Then
    $this->assertTrue($r1);
    $this->assertFalse($r2);
  }

  public function testGetTimeoutValue() {
    // Given
    $apiKey = uniqid();
    $this->getClient()->setApiKey($apiKey);

    // When
    $response = $this->getClient()->getTimeout();

    // Then
    $this->assertEquals(10, $response);
  }

  public function testSetTimeoutValue() {
    // Given
    $data = rand();
    $apiKey = uniqid();
    $this->getClient()->setApiKey($apiKey);
    $this->getClient()->setTimeout($data);

    // When
    $response = $this->getClient()->getTimeout();

    // Then
    $this->assertEquals($data, $response);
  }

  public function testResolveUriAndDataParamsReturnsDefaultResolutionForMethodGet()
  {
    // Given
    $path = uniqid();
    $data = [
      uniqid() => uniqid(),
    ];

    // When
    [$uri, $responseData] = $this->getClient()->resolveUriAndDataParams('GET', $path, $data);

    // Then
    $this->assertEquals((new Uri($path))->withQuery(http_build_query($data)), $uri);
    $this->assertEmpty($responseData);
  }

  public function testResolveUriAndDataParamsReturnsDefaultResolutionForMethodHead()
  {
    // Given
    $path = uniqid();
    $data = [
      uniqid() => uniqid(),
    ];

    // When
    [$uri, $responseData] = $this->getClient()->resolveUriAndDataParams('HEAD', $path, $data);

    // Then
    $this->assertEquals((new Uri($path))->withQuery(http_build_query($data)), $uri);
    $this->assertEmpty($responseData);
  }

  public function testResolveUriAndDataParamsReturnsDefaultResolutionForMethodPost()
  {
    // Given
    $path = uniqid();
    $data = [
      uniqid() => uniqid(),
    ];

    // When
    [$uri, $responseData] = $this->getClient()->resolveUriAndDataParams('POST', $path, $data);

    // Then
    $this->assertEquals(new Uri($path), $uri);
    $this->assertEquals(json_encode($data), $responseData);
  }

  public function testResolveUriAndDataParamsReturnsDefaultResolutionForMethodPostUpload()
  {
    // Given
    $path = uniqid();
    $data = [
      uniqid() => uniqid(),
    ];
    $this->getClient()->setContentTypeRequestValue('multipart/form-data');

    // When
    [$uri, $responseData] = $this->getClient()->resolveUriAndDataParams('POST', $path, $data);

    // Then
    $this->assertEquals(new Uri($path), $uri);
    $this->assertEquals($data, $responseData);
  }

  public function testResolveUriAndDataParamsReturnsCustomResolution()
  {
    // Given
    $path = uniqid();
    $data = [
      uniqid() => uniqid(),
    ];
    $resolveUri = uniqid();
    $resolveData = [
      uniqid() => uniqid(),
    ];
    $callable = function($method, $path, $data) use ($resolveUri, $resolveData) {
      return [$resolveUri, $resolveData];
    };
    $this->getClient()->setUriAndDataParamsResolver($callable);

    // When
    [$uri, $responseData] = $this->getClient()->resolveUriAndDataParams('HEAD', $path, $data);

    // Then
    $this->assertEquals($resolveUri, $uri);
    $this->assertEquals($resolveData, $responseData);
  }

  public function testDataParamModifierReturnsModifiedData()
  {
    // Given
    $path = uniqid();
    $data = [
      uniqid() => uniqid(),
    ];
    $updateData = [
      uniqid() => uniqid(),
    ];
    $callable = function($data) use ($updateData) {
      return array_merge($data, $updateData);
    };
    $this->getClient()->setDataParamModifier($callable);

    // When
    [$uri, $responseData] = $this->getClient()->resolveUriAndDataParams('POST', $path, $data);

    // Then
    $this->assertEquals(json_encode(array_merge($data, $updateData)), $responseData);
  }
}
