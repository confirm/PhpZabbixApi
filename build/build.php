#!/usr/bin/env php
<?php

/*
 * This file is part of PhpZabbixApi.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @copyright The MIT License (MIT)
 * @author confirm IT solutions GmbH, Rathausstrase 14, CH-6340 Baar
 */

if (!in_array(PHP_SAPI, array('cli', 'phpdbg', 'embed'), true)) {
    throw new RuntimeException('Error: '.__FILE__.' must be invoked via the CLI version of PHP, not the '.PHP_SAPI.' SAPI'.PHP_EOL);
}

set_time_limit(0);

// Load required files.
require __DIR__.'/config.inc.php';
require __DIR__.'/replacePlaceholders.func.php';

// Define some pathes and do some sanity checks for existence of the pathes.

if (!is_dir(PATH_ZABBIX)) {
    throw new RuntimeException('ERROR: Zabbix path "'.PATH_ZABBIX.'" is not a directory! Please check the PATH_ZABBIX configuration constant.');
}

// load Zabbix internal constants, to access ZABBIX_API_VERSION
require PATH_ZABBIX.'/include/defines.inc.php';

/**
 * Path to the API.php class file of the Zabbix PHP front-end.
 *
 * This class file will be used, to determine all available API classes.
 */
define('PATH_ZABBIX_API_CLASS_FILE', PATH_ZABBIX.'/include/classes/api/API.php');

if (!file_exists(PATH_ZABBIX_API_CLASS_FILE)) {
    throw new RuntimeException('ERROR: API class file "'.PATH_ZABBIX_API_CLASS_FILE.'" not found! Please check the PATH_ZABBIX_API_CLASS_FILE configuration constant');
}

/**
 * Path to the api/classes/ directory of the Zabbix PHP front-end.
 *
 * This directory and the contained class files will be used, to determine all
 * available methods for each API class.
 */
if (version_compare(ZABBIX_API_VERSION, '2.4') >= 0) {
    define('PATH_ZABBIX_API_CLASSES_DIRECTORY', PATH_ZABBIX.'/include/classes/api/services');
} else {
    define('PATH_ZABBIX_API_CLASSES_DIRECTORY', PATH_ZABBIX.'/api/classes');
}

if (!is_dir(PATH_ZABBIX_API_CLASSES_DIRECTORY)) {
    throw new RuntimeException('ERROR: API class directory "'.PATH_ZABBIX_API_CLASSES_DIRECTORY.'" not found!');
}

// Initialize.

// set template placeholders
$templatePlaceholders = array(
    'INTERFACENAME_ZABBIX_API_INTERFACE' => INTERFACENAME_ZABBIX_API_INTERFACE,
    'CLASSNAME_ZABBIX_API' => CLASSNAME_ZABBIX_API,
    'CLASSNAME_EXCEPTION' => CLASSNAME_EXCEPTION,
    'FILENAME_ZABBIX_API_INTERFACE' => FILENAME_ZABBIX_API_INTERFACE,
    'FILENAME_ZABBIX_API' => FILENAME_ZABBIX_API,
    'FILENAME_EXCEPTION' => FILENAME_EXCEPTION,
);

/*
 * Create class-map class.
 *
 * Create a new class and extend it from the origin Zabbix classes, so that we
 * can fetch the class map directly from the Zabbix classes without defining
 * it here.
 *
 * There are some differences between the Zabbix versions:
 *
 *  < 2.4:  The class map is stored as a static property directly in the
 *          origin API class.
 *
 *  >= 2.4: The class map is stored as an instance property in the
 *          origin CApiServiceFactory class.
 */

// load API
require PATH_ZABBIX_API_CLASS_FILE;

// create new class to fetch class map for API classes
if (version_compare(ZABBIX_API_VERSION, '2.4') >= 0) {
    require PATH_ZABBIX.'/include/classes/core/CRegistryFactory.php';
    require PATH_ZABBIX.'/include/classes/api/CApiServiceFactory.php';
    require PATH_ZABBIX.'/include/classes/api/CApiService.php';

    class ZabbixApiClassMap extends CApiServiceFactory
    {
        public function getClassMap()
        {
            return $this->objects;
        }
    }
} else {
    require PATH_ZABBIX.'/include/classes/api/CZBXAPI.php';

    class ZabbixApiClassMap extends API
    {
        public function getClassMap()
        {
            return self::$classMap;
        }
    }
}

/*
 * Register SPL autoloader.
 *
 * The API class files always inherit from other classes. Most of the classes
 * inherit from the CZBXAPI class, but there are a bunch of classes which
 * are extended by other API classes.
 *
 * So that we don't have to "follow" the right order on loading API class files,
 * we're register an API autoloader right here.
 *
 * Later the get_class_methods() function will automatically invoke this
 * autoloader.
 */
function __autoload($className)
{
    require PATH_ZABBIX_API_CLASSES_DIRECTORY.'/'.$className.'.php';
}

/*
 * Build API array.
 *
 * Just loop through all available API classes and find all defined methods in
 * these classes.
 */

// initialze API array
$apiArray = array();

// Create new instance for API class map.
$apiClassMap = new ZabbixApiClassMap();

// loop through class map
foreach ($apiClassMap->getClassMap() as $resource => $class) {
    // add resource to API array
    $apiArray[$resource] = array();

    // create new reflection class
    $reflection = new ReflectionClass($class);

    // loop through defined methods
    foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC & ~ReflectionMethod::IS_STATIC) as $method) {
        // add action to API array
        if ('CZBXAPI' !== $method->class && !('user' === $resource && 'login' === $method->name) && !('user' === $resource && 'logout' === $method->name) && !$method->isConstructor() && !$method->isDestructor() && !$method->isAbstract()) {
            $apiArray[$resource][] = $method->name;
        }
    }
}

// Build ZabbixApiInterface template.

// get template
if (!$template = file_get_contents(PATH_TEMPLATES.'/ZabbixApiInterface.tpl.php')) {
    throw new RuntimeException('Error.');
}

// fetch API method block
preg_match('/(.*)<!START_API_CONSTANT>(.*)<!END_API_CONSTANT>(.*)<!START_API_METHOD>(.*)<!END_API_METHOD>(.*)/s', $template, $matches);

// sanity check
if (6 !== count($matches)) {
    throw new RuntimeException('Template "'.PATH_TEMPLATES.'/ZabbixApiInterface.tpl.php" parsing failed!');
}

$defines = file_get_contents(PATH_ZABBIX.'/include/defines.inc.php');
preg_match_all('{^define\(\'(?<constant_names>[^\']+)\',\s*(?!\);)(?<constant_values>.+)\)\;.*\n}m', $defines, $constantsArray);

// initialize variable for API constants
$apiConstants = '';

$blacklistedConstants = array('HTTPS', 'ZBX_FONTPATH');

// build API constants
foreach ($constantsArray['constant_names'] as $k => $name) {
    if (0 === strpos($name, 'ZBX_STYLE_') || in_array($name, $blacklistedConstants, true)) {
        continue;
    }
    $value = $constantsArray['constant_values'][$k];

    foreach ($constantsArray['constant_names'] as $declaredName) {
        if (false !== strpos($value, $declaredName)) {
            if (version_compare(PHP_VERSION, '5.6') >= 0) {
                $declaredNameValue = 'self::'.$declaredName;
                $value = preg_replace('#\b'.$declaredName.'\b#', $declaredNameValue, $value);
            } elseif (false !== $declaredNameKey = array_search($declaredName, $constantsArray['constant_names'], true)) {
                $declaredNameValue = $constantsArray['constant_values'][$declaredNameKey];
                $value = eval('return '.preg_replace('#\b'.$declaredName.'\b#', $declaredNameValue, $value).';');
                if (is_string($value)) {
                    $value = '\''.$value.'\'';
                }
            }
        }
    }
    $constantPlaceholders = array(
        'PHP_CONST_NAME' => $name,
        'PHP_CONST_VALUE' => $value,
    );
    $apiConstants .= replacePlaceholders($matches[2], $constantPlaceholders);
}

// initialize variable for API methods
$apiMethods = '';

// build API methods
foreach ($apiArray as $resource => $actions) {
    foreach ($actions as $action) {
        $methodPlaceholders = array(
            'PHP_METHOD' => $resource.ucfirst($action),
        );
        $apiMethods .= replacePlaceholders($matches[4], $methodPlaceholders);
    }
}

// build file content
$fileContent = replacePlaceholders($matches[1].$apiConstants.$matches[3].$apiMethods.$matches[5], $templatePlaceholders);

// write ZabbixApiInterface class
if (!file_put_contents(PATH_BUILD.'/'.FILENAME_ZABBIX_API_INTERFACE, $fileContent)) {
    throw new RuntimeException('Error.');
}

echo 'BUILT: ZabbixApiInterface class file "'.PATH_BUILD.'/'.FILENAME_ZABBIX_API_INTERFACE.'"'."\n";

// Build ZabbixApi template.

// get template
if (!$template = file_get_contents(PATH_TEMPLATES.'/ZabbixApi.tpl.php')) {
    throw new RuntimeException('Error.');
}

// fetch API method block
preg_match('/(.*)<!START_API_METHOD>(.*)<!END_API_METHOD>(.*)/s', $template, $matches);

// sanity check
if (4 !== count($matches)) {
    throw new RuntimeException('Template "'.PATH_TEMPLATES.'/ZabbixApi.tpl.php" parsing failed!');
}

// initialize variable for API methods
$apiMethods = '';

$anonymousFunctions = array(
    'apiinfo.version',
);

// build API methods
foreach ($apiArray as $resource => $actions) {
    foreach ($actions as $action) {
        $apiMethod = $resource.'.'.$action;
        $methodPlaceholders = array(
            'API_METHOD' => $apiMethod,
            'PHP_METHOD' => $resource.ucfirst($action),
            'IS_AUTHENTICATION_REQUIRED' => in_array($apiMethod, $anonymousFunctions, true) ? 'false' : 'true',
        );
        $apiMethods .= replacePlaceholders($matches[2], $methodPlaceholders);
    }
}

$fileContent = replacePlaceholders($matches[1].$apiMethods.$matches[3], $templatePlaceholders);

// write ZabbixApi class
if (!file_put_contents(PATH_BUILD.'/'.FILENAME_ZABBIX_API, $fileContent)) {
    throw new RuntimeException('Error.');
}

echo 'BUILT: ZabbixApi class file "'.PATH_BUILD.'/'.FILENAME_ZABBIX_API.'"'."\n";

// Build Exception template.

// get template
if (!$template = file_get_contents(PATH_TEMPLATES.'/Exception.tpl.php')) {
    throw new RuntimeException('Error.');
}

// build file content
$fileContent = replacePlaceholders($template, $templatePlaceholders);

// write Exception class
if (!file_put_contents(PATH_BUILD.'/'.FILENAME_EXCEPTION, $fileContent)) {
    throw new RuntimeException('Error.');
}

echo 'BUILT: Exception class file "'.PATH_BUILD.'/'.FILENAME_EXCEPTION.'"'."\n";
