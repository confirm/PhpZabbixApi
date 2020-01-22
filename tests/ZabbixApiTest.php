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

namespace ZabbixApi\Tests;

use PHPUnit_Framework_TestCase as TestCase;
use ZabbixApi\ZabbixApi;

/**
 * @author Javier Spagnoletti <phansys@gmail.com>
 */
final class ZabbixApiTest extends TestCase
{
    public function testZabbixApiClass()
    {
        $this->assertTrue(class_exists('ZabbixApi\ZabbixApi'));

        $this->assertGreaterThanOrEqual(0, version_compare(ZabbixApi::ZABBIX_VERSION, '2.4'));

        $zabbix = new ZabbixApi('http://localhost/json_rpc.php', 'zabbix', 'very_secret');

        $defaultParams = array(
            'some_param' => array('one'),
        );
        $zabbix->setDefaultParams($defaultParams);
        $this->assertSame('http://localhost/json_rpc.php', $zabbix->getApiUrl());
        $this->assertSame($defaultParams, $zabbix->getDefaultParams());
        $this->assertSame('', $zabbix->getRequest());
        $this->assertSame('', $zabbix->getResponse());

        $ro = new \ReflectionObject($zabbix);

        $this->assertGreaterThanOrEqual(360, count($ro->getMethods(\ReflectionMethod::IS_PUBLIC)));
        $this->assertGreaterThanOrEqual(668, count($ro->getConstants()));
    }

    public function testUserLoginOnConsecutiveCalls()
    {
        $user = 'zabbix';
        $pass = 'very_secret';
        $authToken = '4u7ht0k3n';
        $cacheDir = __DIR__.'/.token_cache';

        $this->createTokenCacheDir($cacheDir);

        $zabbix = $this->getMockBuilder('ZabbixApi\ZabbixApi')
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->setMethods(array('request', 'userGet'))
            ->getMock();

        // `userGet()` must not be called if the argument 3 (`$tokenCacheDir`) is passed with value `null`.
        $zabbix
            ->expects($this->never())
            ->method('userGet')
            ->with(array('countOutput' => true));

        // `request()` must be called in order to retrieve the token.
        $zabbix
            ->expects($this->once())
            ->method('request')
            ->with('user.login')
            ->willReturn($authToken);

        $this->assertSame($authToken, $zabbix->userLogin(array('user' => $user, 'password' => $pass), '', null));

        $zabbix = $this->getMockBuilder('ZabbixApi\ZabbixApi')
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->setMethods(array('request', 'userGet'))
            ->getMock();

        // `userGet()` must not be called since the token cache file is not created yet.
        $zabbix
            ->expects($this->never())
            ->method('userGet')
            ->with(array('countOutput' => true));

        // `request()` must be called in order to retrieve the token.
        $zabbix
            ->expects($this->once())
            ->method('request')
            ->with('user.login')
            ->willReturn($authToken);

        $this->assertSame($authToken, $zabbix->userLogin(array('user' => $user, 'password' => $pass), '', $cacheDir));

        $zabbix = $this->getMockBuilder('ZabbixApi\ZabbixApi')
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->setMethods(array('request', 'userGet'))
            ->getMock();

        // `userGet()` must be called since the token at token cache file must be validated.
        $zabbix
            ->expects($this->once())
            ->method('userGet')
            ->with(array('countOutput' => true));

        // `request()` must not be called since the token was already retrieved from the token cache file.
        $zabbix
            ->expects($this->never())
            ->method('request')
            ->with('user.login');

        $this->assertSame($authToken, $zabbix->userLogin(array('user' => $user, 'password' => $pass), '', $cacheDir));

        $this->removeTokenCacheDir($cacheDir);
    }

    /**
     * @dataProvider getAuthenticationRequired
     *
     * @param string $method
     * @param string $apiMethod
     * @param bool $isAuthenticationRequired
     */
    public function testAuthenticationRequired($method, $apiMethod, $isAuthenticationRequired)
    {
        $this->assertTrue(is_callable(array('ZabbixApi\ZabbixApi', $method)));

        $zabbix = $this->getMockBuilder('ZabbixApi\ZabbixApi')
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->setMethods(array('request'))
            ->getMock();

        $zabbix
            ->expects($this->once())
            ->method('request')
            ->with($apiMethod, array(), '', $isAuthenticationRequired);

        $zabbix->$method();
    }

    public function getAuthenticationRequired()
    {
        return array(
            array('method' => 'userGet', 'api_method' => 'user.get', 'is_authentication_required' => true),
            array('method' => 'apiinfoVersion', 'api_method' => 'apiinfo.version', 'is_authentication_required' => false),
            array('method' => 'hostGet', 'api_method' => 'host.get', 'is_authentication_required' => true),
        );
    }

    /**
     * @expectedException \ZabbixApi\Exception
     * @expectedExceptionMessage Could not connect to "http://not.found.tld/json_rpc.php"
     */
    public function testZabbixApiConnectionError()
    {
        $zabbix = new ZabbixApi('http://not.found.tld/json_rpc.php', 'zabbix', 'very_secret');

        $zabbix->userGet();
    }

    /**
     * @param string $cacheDir
     */
    private function createTokenCacheDir($cacheDir)
    {
        if (is_dir($cacheDir)) {
            return;
        }

        mkdir($cacheDir);
    }

    /**
     * @param string $cacheDir
     */
    private function removeTokenCacheDir($cacheDir)
    {
        if (!is_dir($cacheDir)) {
            return;
        }

        // Remove the token cache directory.
        foreach (glob($cacheDir.'/{,.}*', GLOB_BRACE) as $file) {
            if (is_dir($file)) {
                continue;
            }
            unlink($file);
        }

        rmdir($cacheDir);
    }
}
