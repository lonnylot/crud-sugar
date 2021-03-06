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

> *NOTE* See [Creating Instances](#creating-instances)

### Registering your endpoints

```php
$client->registerEndpointClass(NumberSearch::class);
```

> *NOTE* See [Endpoint Example Class](#endpoint)
All endpoints must use `\CrudSugar\Concerns\IsEndpoint`

### Use your endpoint

```php
$response = $client->numberSearch->index();
```

## Working With Responses

All requests return a [Response](src/Response.php) object.

> *NOTE*: Response decorates the [GuzzleHTTP Response](http://docs.guzzlephp.org/en/stable/psr7.html#responses). All methods available on the GuzzleHTTP Response are also available through the returned Response object.

### Response API

#### isJson

Returns `true` if the `Content-Type` header contains `\json` or `+json`

#### isSuccessful

Returns `true` if the status code is >= 200 < 300

#### getContent

If `isJson` then returns an associative array. Otherwise returns a string.

### IsEndpoint API

#### setPath(string $path)

Required. The base path to your endpoint. Should not begin or end in a `/`. Should not include any resource specific IDs. Automatically re-builds resource paths.

#### setResources(array $resources)

The list of resources this endpoint has. Defaults to `['index', 'show', 'store', 'update', 'delete']`.

#### setResourcePath(string $resource, string $path)

Set the path for a specific resource. Overrides the resource path generated by `setPath`.

#### setResourceMethod(string $resource, string $method)

Set the method for a specific resource.

## Advanced

### Creating Instances

You can create and instance by calling `\CrudSugar\Client::getInstance()`, but you can also create named instances by calling `\CrudSugar\Client::getInstance('telnyx')`. If you name your instance then you can get that same instance anywhere in your app.

### Endpoints

All endpoints must use the `\CrudSugar\Concerns\IsEndpoint` trait.

#### Resource Validation

This package uses the [Laravel Validator](https://laravel.com/docs/6.x/validation).

> *NOTE* The rules requiring a database (Exists, Unique, etc.) are **not** implemented.

To validate a resource you can make a validation function that returns the [rules](https://laravel.com/docs/6.x/validation#available-validation-rules).

##### Endpoint Example

```php
use CrudSugar\Concerns\IsEndpoint;

class NumberOrder {
  use IsEndpoint;

  public function boot() {
    $this->setResources(['store']);
    $this->setPath('number_orders');
  }

  public function validateIndexRules() {
    return [
      "phone_numbers" => ['required', 'array'],
      "phone_numbers.*.phone_number" => ['required', 'string']
    ];
  }
}
```
