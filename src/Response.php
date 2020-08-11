<?php

namespace CrudSugar;

use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Exception;

class Response {

  private $guzzleResponse;

  private $original;

  private $content = null;

  public function __construct(GuzzleResponse $guzzleResponse = null, Exception $exception = null) {
    $this->guzzleResponse = $guzzleResponse;
    $this->exception = $exception;

    if (!is_null($this->guzzleResponse)) {
      $this->original = $this->guzzleResponse->getBody()->getContents();
    }
  }

  public function isJson() {
    if (is_null($this->guzzleResponse)) {
      return false;
    }

    $contentTypes = $this->guzzleResponse->getHeader('content-type');
    foreach($contentTypes as $contentType) {
      foreach(['/json', '+json'] as $jsonType) {
        if (stripos($contentType, $jsonType) !== false) {
          return true;
        }
      }
    }

    return false;
  }

  public function getContent() {
    if (is_null($this->guzzleResponse) && !is_null($this->exception)) {
      return $this->exception->getMessage();
    }

    if (!is_null($this->content)) {
      return $this->content;
    }

    if ($this->isJson()) {
      $this->content = json_decode($this->original, true);
    } else {
      $this->content = $this->original;
    }

    return $this->content;
  }

  public function isSuccessful() {
    if (is_null($this->guzzleResponse) && !is_null($this->exception)) {
      return false;
    }

    return $this->getStatusCode() >= 200 && $this->getStatusCode() < 300;
  }

  public function __call($name, $arguments) {
    if (method_exists($this->guzzleResponse, $name)) {
      return call_user_func_array([$this->guzzleResponse, $name], $arguments);
    }

    throw new Exception('Could not find "'.$name.'" function.');
  }
}
