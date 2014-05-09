<?php
/**
 * @file    build.php
 *
 * @brief   PHP script to build the PhpZabbixApi class(es).
 *
 * This file is part of PhpZabbixApi.
 *
 * PhpZabbixApi is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * PhpZabbixApi is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with PhpZabbixApi.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @copyright   GNU General Public License
 * @author      confirm IT solutions GmbH, Rathausstrase 14, CH-6340 Baar
 *
 * @version     $Id: build.php 138 2012-10-08 08:00:24Z dbarton $
 */


/*
 * Load required files.
 */

    require 'inc/config.inc.php';
    require 'inc/replacePlaceholders.func.php';

/*
 * Sanity checks.
 */

    if(!file_exists(PATH_ZABBIX_API_CLASS_FILE))
        die('ERROR: API class file "'.PATH_ZABBIX_API_CLASS_FILE.'" not found! Please check the PATH_ZABBIX_API_CLASS_FILE configuration constant');

    if(!file_exists(PATH_ZABBIX_CZBXAPI_CLASS_FILE))
        die('ERROR: CZBXAPI class file "'.PATH_ZABBIX_CZBXAPI_CLASS_FILE.'" not found! Please check the PATH_ZABBIX_CZBXAPI_CLASS_FILE configuration constant');

    if(!is_dir(PATH_ZABBIX_API_CLASSES_DIRECTORY))
        die('ERROR: API class directory "'.PATH_ZABBIX_API_CLASSES_DIRECTORY.'" not found! Please check the PATH_ZABBIX_API_CLASSES_DIRECTORY configuration constant');

/*
 * Initialize.
 */

    // send HTTP header
    header('Content-type: text/plain; charset=utf-8');

    // set template placeholders
    $templatePlaceholders = array(
        'CLASSNAME_ABSTRACT' => CLASSNAME_ABSTRACT,
        'CLASSNAME_CONCRETE' => CLASSNAME_CONCRETE,
        'FILENAME_ABSTRACT'  => FILENAME_ABSTRACT,
        'FILENAME_CONCRETE'  => FILENAME_CONCRETE
    );

/*
 * Load API.
 *
 * Load the origin API of the Zabbix PHP front-end and extend it to get the
 * protected class map array.
 */

    // load API
    require PATH_ZABBIX_API_CLASS_FILE;

    // extend API w/ static function to get protected classMap array
    class API_extended extends API
    {
        public static function getClassMap()
        {
            return self::$classMap;
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

    // load CZBXAPI class
    require PATH_ZABBIX_CZBXAPI_CLASS_FILE;

    // loop through class map
    foreach(API_extended::getClassMap() as $resource => $class)
    {
        // add resource to API array
        $apiArray[$resource] = array();

        // create new reflection class
        $reflection = new ReflectionClass($class);

        // loop through defined methods
        foreach($reflection->getMethods(ReflectionMethod::IS_PUBLIC & ~ReflectionMethod::IS_STATIC) as $method)
        {
            // add action to API array
            if( $method->class != 'CZBXAPI'
                && !($resource == 'user' && $method->name == 'login')
                && !($resource == 'user' && $method->name == 'logout')
                && !$method->isConstructor()
                && !$method->isDestructor()
                && !$method->isAbstract()
            )
                $apiArray[$resource][] = $method->name;
        }

    }

/*
 * Build abstract template.
 */

    // get template
    if(!$template = file_get_contents(PATH_TEMPLATES.'/abstract.tpl.php'))
        die();

    // fetch API method block
    preg_match('/(.*)<!START_API_METHOD>(.*)<!END_API_METHOD>(.*)/s', $template, $matches);

    // sanity check
    if(count($matches) != 4)
        die('Template "'.PATH_TEMPLATES.'"/abstract.tpl.php parsing failed!');

    // initialize variable for API methods
    $apiMethods = '';

    // build API methods
    foreach($apiArray as $resource => $actions)
    {
        foreach($actions as $action)
        {
            $methodPlaceholders = array(
                'API_METHOD' => $resource.'.'.$action,
                'PHP_METHOD' => $resource.ucfirst($action)
            );
            $apiMethods .= replacePlaceholders($matches[2], $methodPlaceholders);
        }
    }

    // build file content
    $fileContent = replacePlaceholders($matches[1].$apiMethods.$matches[3], $templatePlaceholders);

    // write abstract class
    if(!file_put_contents(PATH_BUILD.'/'.FILENAME_ABSTRACT, $fileContent))
        die();

    echo 'BUILT: abstract class file "'.PATH_BUILD.'/'.FILENAME_ABSTRACT.'"'."\n";

/*
 * Build concrete template.
 */

    if(!file_exists(PATH_BUILD.'/'.FILENAME_CONCRETE))
    {
        // get template
        if(!$template = file_get_contents(PATH_TEMPLATES.'/concrete.tpl.php'))
            die();

        // build file content
        $fileContent = replacePlaceholders($template, $templatePlaceholders);

        // write abstract class
        if(!file_put_contents(PATH_BUILD.'/'.FILENAME_CONCRETE, $fileContent))
            die();

        echo 'BUILT: conrete class file "'.PATH_BUILD.'/'.FILENAME_CONCRETE.'"'."\n";
    }
    else
    {
        echo 'SKIPPED: concrete class file "'.PATH_BUILD.'/'.FILENAME_CONCRETE.'"'."\n";
    }

?>
