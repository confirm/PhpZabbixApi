# PhpZabbixApi

The 3.x versions of this package are compatible and tested with Zabbix™ from version 3.0.0 up to 3.4.15.
If you are migrating this package from 2.x to 3.0, please follow the [upgrade notes](UPGRADE-3.0.md).

[![Packagist Version](https://img.shields.io/packagist/v/confirm-it-solutions/php-zabbix-api)](https://packagist.org/packages/confirm-it-solutions/php-zabbix-api)
[![Packagist License](https://img.shields.io/packagist/l/confirm-it-solutions/php-zabbix-api)](https://packagist.org/packages/confirm-it-solutions/php-zabbix-api)

[![Packagist Downloads](https://img.shields.io/packagist/dt/confirm-it-solutions/php-zabbix-api)](https://packagist.org/packages/confirm-it-solutions/php-zabbix-api/stats)

[![Test](https://github.com/confirm/PhpZabbixApi/actions/workflows/test.yaml/badge.svg)](https://github.com/confirm/PhpZabbixApi/actions/workflows/test.yaml)
[![Quality assurance](https://github.com/confirm/PhpZabbixApi/actions/workflows/qa.yaml/badge.svg)](https://github.com/confirm/PhpZabbixApi/actions/workflows/qa.yaml)
[![Lint](https://github.com/confirm/PhpZabbixApi/actions/workflows/lint.yaml/badge.svg)](https://github.com/confirm/PhpZabbixApi/actions/workflows/lint.yaml)

## About

PhpZabbixApi is an open-source PHP SDK to communicate with the [Zabbix JSON-RPC API](https://www.zabbix.com/documentation/3.4/manual/api).

Because this package is generated directly from the origin Zabbix PHP front-end source code,
each real Zabbix JSON-RPC API method is implemented directly as a PHP method. This
means PhpZabbixApi is IDE-friendly, because you've a declared PHP method for each
API method, and there are no PHP magic functions or alike.

## License

PhpZabbixApi is licensed under the [MIT license](LICENSE).

## Installing

Make sure the version of the package you are trying to install is compatible with your Zabbix API version.
If you aren't sure about your Zabbix API version, send a request to the `apiinfo.version` method:

    curl -X POST <your-zabbix-api-endpoint> \
        -H 'Content-Type: application/json-rpc' \
        -d '{"jsonrpc":"2.0","method":"apiinfo.version","params":{},"id":1}'
Replace `<your-zabbix-api-endpoint>` with your Zabbix API endpoint (for example, "https://your-zabbix-domain/api_jsonrpc.php").
Then, you will be able to install the PhpZabbixApi version that is better for you:

    composer require confirm-it-solutions/php-zabbix-api:<version>

All [tagged](https://github.com/confirm/PhpZabbixApi/tags) versions can be installed, for example:

    composer require confirm-it-solutions/php-zabbix-api:^3.0

or:

    composer require confirm-it-solutions/php-zabbix-api:^3.2

The tag names may include [build metadata](https://semver.org/#spec-item-10) (the part
after the plus sign) to easily identify which range of Zabbix API versions are supported.
By instance, the tag `42.1.2+z3.0.0-z3.4.15` denotes that PhpZabbixApi version `42.1.2`
is compatible and tested with Zabbix API from version `3.0.0` to `3.4.15`.

If you're looking for more *bleeding-edge* versions (e.g. for testing), then you
could also use development [branches](https://github.com/confirm-it-solutions/PhpZabbixApi/branches)
by setting a specific [stability flag](https://getcomposer.org/doc/04-schema.md#package-links)
in the version constraint:

    composer require confirm-it-solutions/php-zabbix-api:3.0@dev

## Using the thing

### Naming concept

To translate a Zabbix API call into an SDK method call, you can simply do the following:

1. Remove the dot;
2. Capitalize the first character of the action.

Example:

|Zabbix API         |PHP SDK             |
|-------------------|--------------------|
|`graph.get`        |`graphGet()`        |
|`host.massUpdate`  |`hostMassUpdate()`  |
|`dcheck.isWritable`|`dcheckIsWritable()`|

### Basic usage

To use the PhpZabbixApi you just have to load `ZabbixApi.php`, create a new `ZabbixApi`
instance, and you're ready to go:

```php
<?php
// Load ZabbixApi.

require_once __DIR__.'/vendor/autoload.php';

use Confirm\ZabbixApi\Exception;
use Confirm\ZabbixApi\ZabbixApi;

try {
    // Connect to Zabbix API.
    $api = new ZabbixApi(
        'https://zabbix.confirm.ch/api_jsonrpc.php',
        'zabbix_user',
        'zabbix_password'
    );

    // Do your stuff here.
} catch (Exception $e) {
    // Caught exception from ZabbixApi.
    echo $e->getMessage();
}
```

The API can also work with **HTTP Basic Authroization**, you just have to call the
constructor with additional parameters:

```php
// Connect to Zabbix API through HTTP basic auth.
$api = new ZabbixApi(
    'https://zabbix.confirm.ch/api_jsonrpc.php',
    'zabbix_user',
    'zabbix_password',
    'http_user',
    'http_password'
);
```

If you already have an authentication token, you can pass that value as argument
6 in order to avoid the library to perform the request for the `user.login` method
for requests that require an authenticated user.
If the token is valid, you can omit the argument 2 and 3, since they will be not required:

```php

// This token was previously obtained from a call to the `user.login` method.
$token = 'my_secret_token';

$api = new ZabbixApi(
    'https://zabbix.confirm.ch/api_jsonrpc.php',
    null,
    null,
    null,
    null,
    $token
);

// Make any secured method call.
$api->userGet();
```

### HTTP client

Internally, this package uses the [Guzzle](https://docs.guzzlephp.org/en/stable/)
HTTP client to perform the requests against the Zabbix API.
In order to give you more control and flexibility about the client configuration,
you can pass your own implementation of `\GuzzleHttp\ClientInterface` as argument
7 for `ZabbixApi`:

```php
// Using your own HTTP client.

use GuzzleHttp\Client;

$httpClient = new Client([/* Your own config */]);

$api = new ZabbixApi(
    'https://zabbix.confirm.ch/api_jsonrpc.php',
    'zabbix_user',
    'zabbix_password',
    'http_user',
    'http_password',
    null,
    $httpClient
);
```

Additionally, if you prefer to provide options for the built-in client instead of
provide your own client, you can pass an options array as argument 8:

```php
// Using custom options fot the built-in HTTP client.

use GuzzleHttp\Client;

$httpClientOptions = [/* Your own config */];

$api = new ZabbixApi(
    'https://zabbix.confirm.ch/api_jsonrpc.php',
    'zabbix_user',
    'zabbix_password',
    'http_user',
    'http_password',
    null,
    null,
    $httpClientOptions
);
```

Please, note that argument 7 and 8 cannot be used together. You must choose between
one of both.

### Authentication token caching

In order to improve the response times avoiding the call for the `user.login` method
in each request, you can configure a [PSR-6](https://www.php-fig.org/psr/psr-6/)
caching backend for the authentication token. This way the SDK will get the cached
token after the first login and until its expiration.
The following example uses a fictional `Psr6FilesystemAdapter` class, but you can
choose any [available implementation](https://packagist.org/providers/psr/cache-implementation):

```php
/** @var \Psr\Cache\CacheItemPoolInterface $psr6Cache */
$psr6Cache = new Psr6FilesystemAdapter();
$api->setTokenCache($psr6Cache);
```

## Examples

### Simple request

Here's a simple request to fetch all defined graphs via [`graph.get`](https://www.zabbix.com/documentation/3.4/manual/api/reference/graph/get)
API method:

```php
// Get all graphs.

/** @var array<array<string, mixed>> $graphs */
$graphs = $api->graphGet();

// Print all graph IDs.
foreach ($graphs as $graph) {
    echo $graph['graphid']."\n";
}
```

By default, the values will be returned using an associative array, but you can always
choose to get instances of `\stdClass` instead, using `false` as argument 3 in the
method call:

```php
// Get all graphs as instances of `\stdClass`.

/** @var \stcClass[] $graphs */
$graphs = $api->graphGet([], null, false);

// Print all graph IDs.
foreach ($graphs as $graph) {
    echo $graph->graphid."\n";
}
```

### Request with parameters

Most of the time you want to define some specific parameters.
Here's an example to fetch all CPU graphs via [`graph.get`](https://www.zabbix.com/documentation/3.4/manual/api/reference/graph/get)
API method:

```php
// Get all graphs named "CPU".
$cpuGraphs = $api->graphGet([
    'output' => 'extend',
    'search' => ['name' => 'CPU'],
]);

// Print graph ID with graph name.
foreach ($cpuGraphs as $graph) {
    printf("id:%d name:%s\n", $graph['graphid'], $graph['name']);
}
```

### Define default parameters

Sometimes you want to define default parameters, which will be included in each API
request.
You can do that by defining the parameters in an array via `setDefaultParams()`:

```php
// Use extended output for all further requests.
$api->setDefaultParams([
    'output' => 'extend',
]);

// Get all graphs named "CPU".
$cpuGraphs = $api->graphGet([
    'search' => ['name' => 'CPU'],
]);

// Print graph ID with graph name.
foreach ($cpuGraphs as $graph) {
    printf("id:%d name:%s\n", $graph['graphid'], $graph['name']);
}
```

### Get associative / un-indexed array

By default all API responses will be returned in an indexed array.

So if you then looking for a specific named graph, you've to loop through the indexed
array and compare the `name` attribute of each element. This can be a bit of a pain,
and because of that, there's a simple way to get an associative array instead of
an indexed one. You just have to pass the argument 2 for the method, which is the
name of attribute you'd like to use as a key in the resulting array.

Here's an example to fetch all graphs in an associative array, with the graph's `name`
as array key:

```php
// Get all graphs in an associative array (key=name).
$graphs = $api->graphGet([], 'name');

// Print graph ID with graph name.
if (array_key_exists('CPU Load Zabbix Server', $graphs)) {
    echo 'Graph "CPU Load Zabbix Server" exists.';
} else {
    echo 'Could not find graph "CPU Load Zabbix Server".';
}
```

> **WE ARE LOOKING FOR CONTRIBUTORS, [CLICK HERE FOR MORE INFORMATION](https://github.com/confirm/PhpZabbixApi/issues/28)**
