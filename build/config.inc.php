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

/**
 * Class name for the API interface.
 */
define('INTERFACENAME_ZABBIX_API_INTERFACE', 'ZabbixApiInterface');

/**
 * Filename for the API interface.
 */
define('FILENAME_ZABBIX_API_INTERFACE', INTERFACENAME_ZABBIX_API_INTERFACE.'.php');

/**
 * Class name for the abstract API class.
 */
define('CLASSNAME_ZABBIX_API', 'ZabbixApi');

/**
 * Filename for the abstract API class.
 */
define('FILENAME_ZABBIX_API', CLASSNAME_ZABBIX_API.'.php');

/**
 * Class name for the abstract API class.
 */
define('CLASSNAME_EXCEPTION', 'Exception');

/**
 * Filename for the abstract API class.
 */
define('FILENAME_EXCEPTION', CLASSNAME_EXCEPTION.'.php');

/**
 * Filesystem path to templates directory.
 *
 * This directory contains all templates to build the class files.
 */
define('PATH_TEMPLATES', __DIR__.'/templates');

/**
 * Filesystem path to build directory.
 *
 * This directory contains the built class files.
 */
define('PATH_BUILD', __DIR__.'/../src');

/**
 * Filesystem path to the Zabbix PHP front-end root.
 *
 * Trailing slash not required!
 *
 * This constant is used to set the constants below. So if you've installed
 * the Zabbix PHP front-end (v2) on the same server, you probably only have
 * to update this constant!
 */
define('PATH_ZABBIX', '/opt/zabbix/frontend');
