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

#### $path

Required. The base path to your endpoint. Should not begin or end in a `/`. Should not include any resource specific IDs.

#### $resources

The list of resources this endpoint has. Defaults to `index`, `show`, `store`, `update`, `delete`.

#### $resourceKey

The key used to identify a specific resource. Defaults to `id`.

#### $resourcePaths

A list of resource specific paths. Auto-generates for each `$resource` by appending the `$resourceKey` to the end.


#### $resourceMethods

A mapping from each resource to the HTTP method.

## Advanced

### Creating Instances

You can create and instance by calling `\CrudSugar\Client::getInstance()`, but you can also create named instances by calling `\CrudSugar\Client::getInstance('telnyx')`. If you name your instance then you can get that same instance anywhere in your app.

### Endpoints

All endpoints must use `\CrudSugar\Concerns\IsEndpoint`.

#### Class Variables

#####

```php
use CrudSugar\Concerns\IsEndpoint;

class NumberSearch {
  use IsEndpoint;

  private $path = '/origination/number_searches';

  private $resources = ['index'];
}
```
