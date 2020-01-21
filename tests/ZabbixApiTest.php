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

    /**
     * @expectedException ZabbixApi\Exception
     * @expectedExceptionMessage Could not connect to "http://not.found.tld/api_jsonrpc.php"
     */
    public function testZabbixApiConnectionError()
    {
        new ZabbixApi('http://not.found.tld/api_jsonrpc.php', 'zabbix', 'very_secret_pass');
    }
 

    public function testZabbixApiConnectionNotTriggered()
    {
        $zabbix = new ZabbixApi('http://localhost/api_jsonrpc.php', 'Admin', 'zabbix');

        $this->assertSame('http://localhost/api_jsonrpc.php', $zabbix->getApiUrl());
        return $zabbix; 
    }

    /**
     * @depends testZabbixApiConnectionNotTriggered
     */
    public function testZabbixApiGetHost($zabbix)
    {
        $result = $zabbix->hostGet(array(
            'output' => 'extend',
            'search' => array(
              'host' => 'Zabbix server',
            ),
        ));

        $this->assertCount(1, $result);
        $this->assertObjectHasAttribute('name', $result[0]);
        $this->assertSame('Zabbix server', $result[0]->name);
    }

 }
