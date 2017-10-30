<?php

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

        $rc = new \ReflectionClass('ZabbixApi\ZabbixApi');

        $this->assertGreaterThanOrEqual(405, $rc->getMethods(\ReflectionMethod::IS_PUBLIC));
    }

    public function testZabbixApiConnectionNotTriggered()
    {
        $zabbix = new ZabbixApi('http://localhost/json_rpc.php');
        $zabbix = new ZabbixApi('http://localhost/json_rpc.php', 'zabbix');
        $zabbix = new ZabbixApi('http://localhost/json_rpc.php', '', 'very_secret');

        $this->assertSame('http://localhost/json_rpc.php', $zabbix->getApiUrl());
    }

    /**
     * @expectedException ZabbixApi\Exception
     * @expectedExceptionMessage Could not connect to "http://not.found.tld/json_rpc.php"
     */
    public function testZabbixApiConnectionError()
    {
        new ZabbixApi('http://not.found.tld/json_rpc.php', 'zabbix', 'very_secret_pass');
    }
}
