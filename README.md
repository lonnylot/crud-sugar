# Introduction

This is meant to be a base for new REST APIs. You can use this to get started so you don't have to worry about the basics (i.e.: setting API keys, making the request).

## Composer

You can install the bindings via Composer. Run the following command:

`composer require lonnylot/crud-sugar`

To use the bindings, use Composer's autoload:

`require_once('vendor/autoload.php');`

## Dependencies

The library requires the [GuzzleHTTP](http://docs.guzzlephp.org/en/stable/) library.

## Getting Started

There are three steps to getting endpoints working:

### Setting up your Client

```php
$client = \CrudSugar\Client::getInstance();
$client->setBaseUrl('https://api.telnyx.com/');
$client->setApiKey('secret-key');
```

*NOTE* See [Creating Instances](#creating-instances)

### Registering your endpoints

```php
$client->registerEndpointClass(NumberSearch::class);
```

*NOTE* See [Endpoint Example Class](#example-class)
All endpoints must implement `\CrudSugar\Concerns\Endpoints`

### Use your endpoint

```php
$response = $client->numberSearch->all();
```

## Example Endpoint

All endpoints must implement `CrudSugar\Concerns\Endpoints`.

```php
use CrudSugar\Concerns\Endpoints;
use CrudSugar\Client;

class NumberSearch implements Endpoints {
  private $client;

  private $endpoint = '/origination/number_searches';

  public function __construct(Client $client) {
    $this->client = $client;
  }

  public function all($params) {
    return $this->client->request('GET', $this->endpoint, $params);
  }
}
```

## Creating Instances

You can create and instance by calling `\CrudSugar\Client::getInstance()`, but you can also create named instances by calling `\CrudSugar\Client::getInstance('telnyx')`. If you name your instance then you can get that same instance anywhere in your app.
