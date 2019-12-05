<?php

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use CrudSugar\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

abstract class TestCase extends BaseTestCase {
  protected function setUp(): void {
    $mock = new MockHandler([
        new Response(200, ['X-Foo' => 'Bar'])
    ]);
    $handler = HandlerStack::create($mock);
    $this->getClient()->setHandler($handler);
  }

  protected function tearDown(): void {
    $this->getClient()->reset();
  }

  public function getClient() {
    return Client::getInstance('Test');
  }
}
