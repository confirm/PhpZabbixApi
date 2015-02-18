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

define('PATH_ZABBIX', '/usr/share/zabbix');
?>
