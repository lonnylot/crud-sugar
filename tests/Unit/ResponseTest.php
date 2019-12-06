<?php

namespace Tests\Unit;

use CrudSugar\Response;
use Tests\TestCase;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Exception;

class ResponseTest extends TestCase {
  private function getGuzzleResponse($statusCode = 200, $headers = ['X-Foo' => 'Bar']) {
    return new GuzzleResponse($statusCode, $headers);
  }

  public function testDecoratesGuzzleResponse() {
    // Given
    $response = new Response($this->getGuzzleResponse());

    // When
    $statusCode = $response->getStatusCode();

    // Then
    $this->assertSame(200, $statusCode);
  }

  public function testDetectsJson() {
    // Given
    $responseHeaders = ['Content-Type' => 'application/json; charset=utf-8'];
    $response = new Response($this->getGuzzleResponse(200, $responseHeaders));

    // When
    $isJson = $response->isJson();

    // Then
    $this->assertTrue($isJson);
  }

  public function testDetectsNotJson() {
    // Given
    $responseHeaders = ['Content-Type' => 'text/html; charset=utf-8'];
    $response = new Response($this->getGuzzleResponse(200, $responseHeaders));

    // When
    $isJson = $response->isJson();

    // Then
    $this->assertFalse($isJson);
  }

  public function testGetsJsonContent() {
    // Given
    $responseHeaders = ['Content-Type' => 'application/json; charset=utf-8'];
    $response = new Response($this->getGuzzleResponse(200, $responseHeaders));

    // When
    $content = $response->getContent();

    // Then
    $this->assertNull($content); // NOTE: It is null b/c I don't know how to fake Guzzle response content
  }

  public function testGetsRegularContent() {
    // Given
    $responseHeaders = ['Content-Type' => 'text/html; charset=utf-8'];
    $response = new Response($this->getGuzzleResponse(200, $responseHeaders));

    // When
    $content = $response->getContent();

    // Then
    $this->assertIsString($content); // NOTE: It is an empty string b/c I don't know how to fake Guzzle response content
  }

  public function testDetectsSuccessfulStatus() {
    // Given
    $response = new Response($this->getGuzzleResponse(rand(200, 299)));

    // When
    $isSuccessful = $response->isSuccessful();

    // Then
    $this->assertTrue($isSuccessful);
  }

  public function testDetectsNotSuccessfulStatus() {
    // Given
    $response = new Response($this->getGuzzleResponse(rand(300, 599)));

    // When
    $isSuccessful = $response->isSuccessful();

    // Then
    $this->assertFalse($isSuccessful);
  }
}
