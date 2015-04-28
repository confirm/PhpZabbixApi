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

### Basic usage

To use the PhpZabbixApi you just have to load `ZabbixApi.class.php` and you're ready to go:

```php
<?php

// load ZabbixApi
require_once 'lib/ZabbixApi.class.php';

try {

    // connect to Zabbix API
    $api = new ZabbixApi('http://zabbix.confirm.ch/api_jsonrpc.php', 'zabbix', 'admin');
    
    // do your stuff here

} catch(Exception $e) {

    // Exception in ZabbixApi catched
    echo $e->getMessage();

}
?>
```

## Examples

*coming soon*
