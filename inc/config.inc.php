<?php
/**
 * @file    config.inc.php
 *
 * @brief   Configuration file of the PhPZabbixApi builder.
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
 * @version     $Id: config.inc.php 134 2012-08-20 15:44:55Z dbarton $
 */

/**
 * @brief   Class name for the abstract API class.
 */

define('CLASSNAME_ABSTRACT', 'ZabbixApiAbstract');

/**
 * @brief   Filename for the abstract API class.
 */

define('FILENAME_ABSTRACT', CLASSNAME_ABSTRACT.'.class.php');

/**
 * @brief   Class name for the concrete API class.
 */

define('CLASSNAME_CONCRETE', 'ZabbixApi');

/**
 * @brief   Filename for the abstract API class.
 */

define('FILENAME_CONCRETE', CLASSNAME_CONCRETE.'.class.php');

/**
 * @brief   Filesystem path to templates directory.
 *
 * This directory contains all templates to build the class files.
 */

define('PATH_TEMPLATES', 'templates');

/**
 * @brief   Filesystem path to build directory.
 *
 * This directory contains the built class files.
 */

define('PATH_BUILD', 'build');

/**
 * @brief   Filesystem path to the Zabbix PHP front-end root.
 *
 * Trailing slash not required!
 *
 * This constant is used to set the constants below. So if you've installed
 * the Zabbix PHP front-end (v2) on the same server, you probably only have
 * to update this constant!
 */

define('PATH_ZABBIX', '../zabbix');

/**
 * @brief   Path to the API.php class file of the Zabbix PHP front-end.
 *
 * This class file will be used, to determine all available API classes.
 */

define('PATH_ZABBIX_API_CLASS_FILE', PATH_ZABBIX.'/include/classes/api/API.php');

/**
 * @brief   Path to the CZBXAPI.php class file of the Zabbix PHP front-end.
 *
 * This class file is required by all API class files, because they're
 * inherit from the contained CZBXAPI class.
 */

define('PATH_ZABBIX_CZBXAPI_CLASS_FILE', PATH_ZABBIX.'/include/classes/api/CZBXAPI.php');

/**
 * @brief   Path to the api/classes/ directory of the Zabbix PHP front-end.
 *
 * Trailing slash not required!
 *
 * This directory and the contained class files will be used, to determine all
 * available methods for each API class.
 */

define('PATH_ZABBIX_API_CLASSES_DIRECTORY', PATH_ZABBIX.'/api/classes');
?>
