UPGRADE FROM 2.x to 3.0
=======================

## Supported PHP versions

The support for PHP < 5.6 has been removed.

## Supported Zabbix versions

The Zabbix support is provided and tested from version 3.0.0 up to 3.4.15.

## Install process and package contents

Since PhpZabbixApi depends on some PHP extensions and third party libraries, you
must look for a release compatible with your Zabbix API version and use [Composer](https://getcomposer.org/)
to install the package and its dependencies:

    composer require confirm-it-solutions/php-zabbix-api:^3.0

Unlike previous versions, the fully functional SDK is provided by this package,
so you don't need to run additional commands to create the library classes.

## Scaffolding capabilities

The `build/` directory and the scaffolding capabilities have been removed from this package.
They are provided by the [`confirm-it-solutions/zabbix-php-sdk-builder`](https://github.com/phansys/zabbix-php-sdk-builder) package.

## `ZabbixApi` namespace

The `ZabbixApi` namespace was replaced by `Confirm\ZabbixApi`, in order to make explicit the vendor name for this package.

## `ZabbixApi` and `AbstractZabbixApi` classes

The `ZabbixApi` class is declared as final, so it can not be extended anymore. This allows us to make more changes
in minor releases without breaking the BC promise.
The method `request()` was changed its visibility from public to private.
Other method's signatures were updated in order to support more features.
A third argument was added to every API method in order to choose if the result must
be returned as an instance of `\stdClass` or as an associative array.

Before:
```php
public function __construct(
    $apiUrl = '',
    $user = '',
    $password = '',
    $httpUser = '',
    $httpPassword = '',
    $authToken = '',
    $sslContext = null
) {
    // ...
}

public function hostGet($params = [], $arrayKeyProperty = '')
{
    // ...
}
```

After:
```php
public function __construct(
    $apiUrl = null,
    $user = null,
    $password = null,
    $httpUser = null,
    $httpPassword = null,
    $authToken = null,
    \GuzzleHttp\ClientInterface $client = null,
    array $clientOptions = []
) {
    // ...
}

public function hostGet($params = [], $arrayKeyProperty = null, $assoc = true)
{
    // ...
}
```

The `AbstractZabbixApi` class was removed without replacement.

## `ZabbixApiInterface` interface

Interface `ZabbixApiInterface` is introduced in order to declare the contract for the
methods defined in the current supported API version. This interface is implemented by `ZabbixApi`
and allows you to provide your own implementation in case you need to extend some features.

## HTTP client

This package now relies on the [Guzzle](https://docs.guzzlephp.org/en/stable/) HTTP client to interact with the
ReST RPC API.
In order to give you more flexibility, you can provide your own pre-configured client or pass custom options to
the built-in client.

## Authentication token caching

Unless it is explicitly configured, the `ZabbixApi` class does not provide filesystem
based caching for the authentication token anymore.
It implements the `TokenCacheAwareInterface` interface, which declares the `setTokenCache()`
method for this purpose. It accepts an instance of [PSR-6](https://www.php-fig.org/psr/psr-6/)
`\Psr\Cache\CacheItemPoolInterface`, this way you can choose between a [wide variety](https://packagist.org/providers/psr/cache-implementation)
of standardized caching backends for the authentication token.
