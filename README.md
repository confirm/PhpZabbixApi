## PhpZabbixApi

PhpZabbixApi is an open-source PHP class library to communicate with the Zabbix™ JSON-RPC API.

## Building

The Zabbix™ 2.0 PHP front-end is written in PHP, therefore we're able to build the PHP library directly from the origin Zabbix™ 2.0 PHP front-end source code / files.

If you want to build your own library, have a look at the configuration file `inc/config.inc.php`.  
You might want to point `PATH_ZABBIX`  to your Zabbix™ installation directory.

There are also pre-built libraries available in the `build/` directory, if you don't want to build it yourself.

## Using

Build your own library or download the pre-built libraries from the `build/` directory.  
Make sure the version of the build matches the Zabbix™ PHP front-end version.

### Download

[Download](https://github.com/domibarton/PhpZabbixApi/tree/master/build)  pre-built PHP class library files directly from gitub (`build/` directory).

Make sure you've downloaded the following files and stored them in the same directory:

* `ZabbixApi.class.php` 
* `ZabbixApiAbstract.class.php`

For example:

```
my_application
├── index.php
└── lib
    ├── ZabbixApiAbstract.class.php
    └── ZabbixApi.class.php
```

### Naming concept

To translate a Zabbix™ API call into a PHP method call, you can simply

1. remove the dot
2. capitalize the first letter of the action

Example:

```
Zabbix™ API         PHP API
-----------         -------
graph.get           graphGet()
host.massUpdate     hostMassUpdate()
dcheck.isWritable   dcheckIsWritable()
```

### Customizing the API class

By default there are only 2 classes defined:

```
ZabbixApiAbstract
└── ZabbixApi
```

If you want to customize or extend the library, you might want to do that in the `ZabbixApi` class.  
Out of the box, `ZabbixApi` is an empty class inherited from `ZabbixApiAbstract`.

By customizing only `ZabbixApi`, you're able to update `ZabbixApiAbstract` (the build) at any time, without merging your customizations manually.

### Basic usage

To use the PhpZabbixApi you just have to load `ZabbixApi.class.php`, create a new `ZabbixApi` instance, and you're ready to go:

```php
<?php
// load ZabbixApi
require_once 'lib/ZabbixApi.class.php';

try 
{
    // connect to Zabbix API
    $api = new ZabbixApi('http://zabbix.confirm.ch/api_jsonrpc.php', 'zabbix', 'admin');
    
    /* ... do your stuff here ... */
} 
catch(Exception $e) 
{
    // Exception in ZabbixApi catched
    echo $e->getMessage();
}
?>
```

## Examples

Please see also [the old project page](http://zabbixapi.confirm.ch/) for more examples.  

### Simple request

```php
<?php

// load ZabbixApi
require 'ZabbixApi.class.php';

try
{
    // connect to Zabbix API
    $api = new ZabbixApi('http://zabbix.confirm.ch/api_jsonrpc.php', 'zabbix', 'admin');

    // get all graphs
    $graphs = $api->graphGet();

    // print all graph IDs
    foreach($graphs as $graph)
        echo $graph->graphid."\n";
} 
catch(Exception $e) 
{
    // Exception in ZabbixApi catched
    echo $e->getMessage();
}
?>
```

