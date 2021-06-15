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

namespace Confirm\ZabbixApi\Tests;

use Confirm\ZabbixApi\Exception;
use Confirm\ZabbixApi\TokenCacheAwareInterface;
use Confirm\ZabbixApi\ZabbixApi;
use Confirm\ZabbixApi\ZabbixApiInterface;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\RequestOptions;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

/**
 * @author Javier Spagnoletti <phansys@gmail.com>
 */
final class ZabbixApiTest extends TestCase
{
    public function testZabbixApiClass()
    {
        $this->assertTrue(class_exists(ZabbixApi::class));
        $this->assertTrue(is_subclass_of(ZabbixApi::class, ZabbixApiInterface::class));
        $this->assertTrue(is_subclass_of(ZabbixApi::class, TokenCacheAwareInterface::class));

        $this->assertGreaterThanOrEqual(0, version_compare(ZabbixApiInterface::ZABBIX_VERSION, '3.0'));
        $apiUrl = 'https://localhost/json_rpc.php';

        $zabbix = new ZabbixApi($apiUrl, 'zabbix', 'very_secret');

        $defaultParams = [
            'some_param' => ['one'],
        ];
        $zabbix->setDefaultParams($defaultParams);
        $this->assertSame($apiUrl, $zabbix->getApiUrl());
        $this->assertSame($defaultParams, $zabbix->getDefaultParams());
        $this->assertNull($zabbix->getResponse());

        $ro = new \ReflectionObject($zabbix);

        $this->assertGreaterThanOrEqual(668, count($ro->getConstants()));
    }

    public function testAuthenticationTokenCache()
    {
        $url = 'https://local.zabbix.tld/json_rpc.php';
        $user = 'zabbix';
        $pass = 'very_secret';
        $authToken = '4u7ht0k3n';

        $httpClient = $this->createMock(ClientInterface::class);

        $zabbix = new ZabbixApi($url, $user, $pass, null, null, null, $httpClient);
        $psr6Cache = new ArrayAdapter();
        $zabbix->setTokenCache($psr6Cache);

        $httpClient
            ->expects($this->exactly(2))
            ->method('request')
            ->with('POST', $url, $this->callback(function (array $jsonPayload) {
                return in_array($jsonPayload[RequestOptions::JSON]['method'], ['user.login', 'trigger.get'], true);
            }))
            ->willReturnCallback(function ($method, $uri, array $options) use ($authToken) {
                $streamResponse = $this->createMock(StreamInterface::class);

                if ('user.login' === $options[RequestOptions::JSON]['method']) {
                    $streamResponse
                        ->expects($this->atLeastOnce())
                        ->method('__toString')
                        ->willReturn(json_encode(['result' => $authToken]));
                } elseif ('trigger.get' === $options[RequestOptions::JSON]['method']) {
                    $streamResponse
                        ->expects($this->atLeastOnce())
                        ->method('__toString')
                        ->willReturn(json_encode(['result' => []]));
                }

                $response = $this->createMock(ResponseInterface::class);
                $response
                    ->method('getBody')
                    ->willReturn($streamResponse);

                return $response;
            });

        $this->assertEmpty($psr6Cache->getValues());

        $this->assertSame([], $zabbix->triggerGet());
        $this->assertNotEmpty($psr6Cache->getValues());

        $tokenCacheKey = array_keys($psr6Cache->getValues())[0];

        $tokenCacheItem = $psr6Cache->getItem($tokenCacheKey);

        $this->assertTrue($tokenCacheItem->isHit());
        $this->assertSame($authToken, $tokenCacheItem->get());

        $psr6Cache->clear();
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
        $this->assertTrue(method_exists(ZabbixApiInterface::class, $method));

        $url = 'https://local.zabbix.tld/json_rpc.php';
        $user = 'zabbix';
        $pass = 'very_secret';
        $authToken = '4u7ht0k3n';

        $httpClient = $this->createMock(ClientInterface::class);
        $httpClient
            ->expects($this->exactly($isAuthenticationRequired ? 2 : 1))
            ->method('request')
            ->with('POST', $url, $this->callback(function (array $jsonPayload) use ($apiMethod) {
                return in_array($jsonPayload[RequestOptions::JSON]['method'], ['user.login', $apiMethod], true);
            }))
            ->willReturnCallback(function ($method, $uri, array $options) use ($url, $authToken, $apiMethod) {
                $this->assertSame('POST', $method);
                $this->assertSame($url, $uri);

                $streamResponse = $this->createMock(StreamInterface::class);

                if ('user.login' === $options[RequestOptions::JSON]['method']) {
                    $streamResponse
                        ->expects($this->atLeastOnce())
                        ->method('__toString')
                        ->willReturn(json_encode(['result' => $authToken]));
                } elseif ($apiMethod === $options[RequestOptions::JSON]['method']) {
                    $streamResponse
                        ->expects($this->atLeastOnce())
                        ->method('__toString')
                        ->willReturn(json_encode(['result' => []]));
                }

                $response = $this->createMock(ResponseInterface::class);
                $response
                    ->method('getBody')
                    ->willReturn($streamResponse);

                return $response;
            });

        $zabbix = new ZabbixApi($url, $user, $pass, null, null, null, $httpClient);

        $this->assertIsCallable([$zabbix, $method]);

        $zabbix->{$method}();
    }

    public function getAuthenticationRequired()
    {
        yield ['method' => 'userGet', 'api_method' => 'user.get', 'is_authentication_required' => true];
        yield ['method' => 'apiinfoVersion', 'api_method' => 'apiinfo.version', 'is_authentication_required' => false];
        yield ['method' => 'userLogin', 'api_method' => 'apiinfo.version', 'is_authentication_required' => false];
        yield ['method' => 'hostGet', 'api_method' => 'host.get', 'is_authentication_required' => true];
    }

    public function testZabbixApiConnectionError()
    {
        $zabbix = new ZabbixApi('https://not.found.tld/json_rpc.php', 'zabbix', 'very_secret');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('cURL error 6: Could not resolve host: not.found.tld (see https://curl.haxx.se/libcurl/c/libcurl-errors.html)');

        $zabbix->userGet();
    }

    public function testUserAgentHeader()
    {
        $zabbix = new ZabbixApi('https://not.found.tld/json_rpc.php');

        $getHttpClient = \Closure::bind(function () {
            return $this->client;
        }, $zabbix, ZabbixApi::class);

        $getHttpClientConfig = \Closure::bind(function () {
            return $this->config;
        }, $getHttpClient(), Client::class);

        $expectedUserAgentRegex = sprintf('#^PhpZabbixApi/%s GuzzleHttp/(6|7).*$#', ZabbixApiInterface::PHP_ZABBIX_API_VERSION);

        $this->assertMatchesRegularExpression($expectedUserAgentRegex, $getHttpClientConfig()[RequestOptions::HEADERS]['User-Agent']);
    }

    /**
     * @dataProvider methodArgumentsProvider
     *
     * @param string|int $firstResultKey
     * @param string $apiMethod
     * @param string $method
     * @param string|null $arrayKeyProperty
     * @param bool|null $assoc
     */
    public function testMethodArguments(array $params, array $responsePayload, $firstResultKey, $apiMethod, $method, $arrayKeyProperty = null, $assoc = null)
    {
        $url = 'https://local.zabbix.tld/json_rpc.php';

        $streamResponse = $this->createMock(StreamInterface::class);
        $streamResponse
            ->expects($this->once())
            ->method('__toString')
            ->willReturn(json_encode($responsePayload));

        $response = $this->createMock(ResponseInterface::class);
        $response
            ->method('getBody')
            ->willReturn($streamResponse);

        $httpClient = $this->createMock(ClientInterface::class);
        $httpClient
            ->expects($this->once())
            ->method('request')
            ->with('POST', $url, $this->callback(function (array $jsonPayload) use ($params, $apiMethod) {
                return $jsonPayload[RequestOptions::JSON]['method'] === $apiMethod &&
                    $jsonPayload[RequestOptions::JSON]['params'] === $params;
            }))
            ->willReturn($response);

        $zabbix = new ZabbixApi($url, null, null, null, null, null, $httpClient);

        if (null === $assoc) {
            // Let the method to use its default value.
            $result = $zabbix->{$method}($params, $arrayKeyProperty);
        } else {
            $result = $zabbix->{$method}($params, $arrayKeyProperty, $assoc);
        }

        $this->assertIsArray($result);
        $this->assertArrayHasKey($firstResultKey, $result);
        $this->assertArrayNotHasKey('jsonrpc', $result);
        $this->assertArrayNotHasKey('result', $result);
        $this->assertCount(count($responsePayload['result']), $result);

        if (true === $assoc || null === $assoc) {
            // By default, results are returned as array if `$assoc` argument is ommited.
            $this->assertIsArray($result[$firstResultKey]);
            $this->assertArrayHasKey(array_keys($responsePayload['result'][0])[0], $result[$firstResultKey]);
        } else {
            $this->assertInstanceOf(\stdClass::class, $result[$firstResultKey]);
            $this->assertObjectHasAttribute(array_keys($responsePayload['result'][0])[0], $result[$firstResultKey]);
        }
    }

    public function methodArgumentsProvider()
    {
        yield [
            ['hostids' => '12940'],
            [
                'jsonrpc' => '2.0',
                'result' => [
                    [
                        'hostid' => '12940',
                        'proxy_hostid' => '0',
                        'host' => '93ff62e8ed57737e397ce83220fc9a0d834fe5c814f5154d68cccc2772687dad',
                        'status' => '0',
                        'disable_until' => '0',
                        'error' => '',
                        'available' => '0',
                        'errors_from' => '0',
                        'lastaccess' => '0',
                        'ipmi_authtype' => '0',
                        'ipmi_privilege' => '2',
                        'ipmi_username' => '',
                        'ipmi_password' => '',
                        'ipmi_disable_until' => '0',
                        'ipmi_available' => '0',
                        'snmp_disable_until' => '0',
                        'snmp_available' => '0',
                        'maintenanceid' => '0',
                        'maintenance_status' => '0',
                        'maintenance_type' => '0',
                        'maintenance_from' => '0',
                        'ipmi_errors_from' => '0',
                        'snmp_errors_from' => '0',
                        'ipmi_error' => '',
                        'snmp_error' => '',
                        'jmx_disable_until' => '0',
                        'jmx_available' => '0',
                        'jmx_errors_from' => '0',
                        'jmx_error' => '',
                        'name' => 'media1.tilatina.com - 7337 - tilatina',
                        'flags' => '0',
                        'templateid' => '0',
                        'description' => '',
                        'tls_connect' => '1',
                        'tls_accept' => '1',
                        'tls_issuer' => '',
                        'tls_subject' => '',
                        'tls_psk_identity' => '',
                        'tls_psk' => '',
                        'proxy_address' => '',
                        'auto_compress' => '1',
                        'inventory_mode' => '-1',
                    ],
                ],
            ],
            0,
            'host.get',
            'hostGet',
            null,
            true,
        ];

        yield [
            ['hostids' => '12940'],
            [
                'jsonrpc' => '2.0',
                'result' => [
                    [
                        'hostid' => '12940',
                        'proxy_hostid' => '0',
                        'host' => '93ff62e8ed57737e397ce83220fc9a0d834fe5c814f5154d68cccc2772687dad',
                        'status' => '0',
                        'disable_until' => '0',
                        'error' => '',
                        'available' => '0',
                        'errors_from' => '0',
                        'lastaccess' => '0',
                        'ipmi_authtype' => '0',
                        'ipmi_privilege' => '2',
                        'ipmi_username' => '',
                        'ipmi_password' => '',
                        'ipmi_disable_until' => '0',
                        'ipmi_available' => '0',
                        'snmp_disable_until' => '0',
                        'snmp_available' => '0',
                        'maintenanceid' => '0',
                        'maintenance_status' => '0',
                        'maintenance_type' => '0',
                        'maintenance_from' => '0',
                        'ipmi_errors_from' => '0',
                        'snmp_errors_from' => '0',
                        'ipmi_error' => '',
                        'snmp_error' => '',
                        'jmx_disable_until' => '0',
                        'jmx_available' => '0',
                        'jmx_errors_from' => '0',
                        'jmx_error' => '',
                        'name' => 'media1.tilatina.com - 7337 - tilatina',
                        'flags' => '0',
                        'templateid' => '0',
                        'description' => '',
                        'tls_connect' => '1',
                        'tls_accept' => '1',
                        'tls_issuer' => '',
                        'tls_subject' => '',
                        'tls_psk_identity' => '',
                        'tls_psk' => '',
                        'proxy_address' => '',
                        'auto_compress' => '1',
                        'inventory_mode' => '-1',
                    ],
                ],
            ],
            0,
            'host.get',
            'hostGet',
            null,
            false,
        ];

        yield [
            ['hostids' => '12940'],
            [
                'jsonrpc' => '2.0',
                'result' => [
                    [
                        'hostid' => '12940',
                        'proxy_hostid' => '0',
                        'host' => '93ff62e8ed57737e397ce83220fc9a0d834fe5c814f5154d68cccc2772687dad',
                        'status' => '0',
                        'disable_until' => '0',
                        'error' => '',
                        'available' => '0',
                        'errors_from' => '0',
                        'lastaccess' => '0',
                        'ipmi_authtype' => '0',
                        'ipmi_privilege' => '2',
                        'ipmi_username' => '',
                        'ipmi_password' => '',
                        'ipmi_disable_until' => '0',
                        'ipmi_available' => '0',
                        'snmp_disable_until' => '0',
                        'snmp_available' => '0',
                        'maintenanceid' => '0',
                        'maintenance_status' => '0',
                        'maintenance_type' => '0',
                        'maintenance_from' => '0',
                        'ipmi_errors_from' => '0',
                        'snmp_errors_from' => '0',
                        'ipmi_error' => '',
                        'snmp_error' => '',
                        'jmx_disable_until' => '0',
                        'jmx_available' => '0',
                        'jmx_errors_from' => '0',
                        'jmx_error' => '',
                        'name' => 'media1.tilatina.com - 7337 - tilatina',
                        'flags' => '0',
                        'templateid' => '0',
                        'description' => '',
                        'tls_connect' => '1',
                        'tls_accept' => '1',
                        'tls_issuer' => '',
                        'tls_subject' => '',
                        'tls_psk_identity' => '',
                        'tls_psk' => '',
                        'proxy_address' => '',
                        'auto_compress' => '1',
                        'inventory_mode' => '-1',
                    ],
                ],
            ],
            0,
            'host.get',
            'hostGet',
        ];

        yield 'Using array in "hostids" param' => [
            ['hostids' => ['12940']],
            [
                'jsonrpc' => '2.0',
                'result' => [
                    [
                        'hostid' => '12940',
                        'proxy_hostid' => '0',
                        'host' => '93ff62e8ed57737e397ce83220fc9a0d834fe5c814f5154d68cccc2772687dad',
                        'status' => '0',
                        'disable_until' => '0',
                        'error' => '',
                        'available' => '0',
                        'errors_from' => '0',
                        'lastaccess' => '0',
                        'ipmi_authtype' => '0',
                        'ipmi_privilege' => '2',
                        'ipmi_username' => '',
                        'ipmi_password' => '',
                        'ipmi_disable_until' => '0',
                        'ipmi_available' => '0',
                        'snmp_disable_until' => '0',
                        'snmp_available' => '0',
                        'maintenanceid' => '0',
                        'maintenance_status' => '0',
                        'maintenance_type' => '0',
                        'maintenance_from' => '0',
                        'ipmi_errors_from' => '0',
                        'snmp_errors_from' => '0',
                        'ipmi_error' => '',
                        'snmp_error' => '',
                        'jmx_disable_until' => '0',
                        'jmx_available' => '0',
                        'jmx_errors_from' => '0',
                        'jmx_error' => '',
                        'name' => 'media1.tilatina.com - 7337 - tilatina',
                        'flags' => '0',
                        'templateid' => '0',
                        'description' => '',
                        'tls_connect' => '1',
                        'tls_accept' => '1',
                        'tls_issuer' => '',
                        'tls_subject' => '',
                        'tls_psk_identity' => '',
                        'tls_psk' => '',
                        'proxy_address' => '',
                        'auto_compress' => '1',
                        'inventory_mode' => '-1',
                    ],
                ],
            ],
            0,
            'host.get',
            'hostGet',
        ];

        yield 'Setting a specific array key' => [
            ['hostids' => ['12940']],
            [
                'jsonrpc' => '2.0',
                'result' => [
                    [
                        'hostid' => '12940',
                        'proxy_hostid' => '0',
                        'host' => '93ff62e8ed57737e397ce83220fc9a0d834fe5c814f5154d68cccc2772687dad',
                        'status' => '0',
                        'disable_until' => '0',
                        'error' => '',
                        'available' => '0',
                        'errors_from' => '0',
                        'lastaccess' => '0',
                        'ipmi_authtype' => '0',
                        'ipmi_privilege' => '2',
                        'ipmi_username' => '',
                        'ipmi_password' => '',
                        'ipmi_disable_until' => '0',
                        'ipmi_available' => '0',
                        'snmp_disable_until' => '0',
                        'snmp_available' => '0',
                        'maintenanceid' => '0',
                        'maintenance_status' => '0',
                        'maintenance_type' => '0',
                        'maintenance_from' => '0',
                        'ipmi_errors_from' => '0',
                        'snmp_errors_from' => '0',
                        'ipmi_error' => '',
                        'snmp_error' => '',
                        'jmx_disable_until' => '0',
                        'jmx_available' => '0',
                        'jmx_errors_from' => '0',
                        'jmx_error' => '',
                        'name' => 'media1.tilatina.com - 7337 - tilatina',
                        'flags' => '0',
                        'templateid' => '0',
                        'description' => '',
                        'tls_connect' => '1',
                        'tls_accept' => '1',
                        'tls_issuer' => '',
                        'tls_subject' => '',
                        'tls_psk_identity' => '',
                        'tls_psk' => '',
                        'proxy_address' => '',
                        'auto_compress' => '1',
                        'inventory_mode' => '-1',
                    ],
                ],
            ],
            '12940',
            'host.get',
            'hostGet',
            'hostid',
        ];
    }

    /**
     * @dataProvider provideMethodNames
     *
     * @param string $methodName
     */
    public function testZabbixApiMethods($methodName)
    {
        $this->assertTrue(method_exists(ZabbixApiInterface::class, $methodName));
    }

    public function provideMethodNames()
    {
        yield ['actionCreate'];
        yield ['actionDelete'];
        yield ['actionGet'];
        yield ['actionPk'];
        yield ['actionPkOption'];
        yield ['actionTableName'];
        yield ['actionUpdate'];
        yield ['actionValidateOperationConditions'];
        yield ['actionValidateOperationsIntegrity'];
        yield ['alertGet'];
        yield ['alertPk'];
        yield ['alertPkOption'];
        yield ['alertTableName'];
        yield ['apiPk'];
        yield ['apiPkOption'];
        yield ['apiTableName'];
        yield ['apiinfoPk'];
        yield ['apiinfoPkOption'];
        yield ['apiinfoTableName'];
        yield ['apiinfoVersion'];
        yield ['applicationCreate'];
        yield ['applicationDelete'];
        yield ['applicationGet'];
        yield ['applicationMassAdd'];
        yield ['applicationPk'];
        yield ['applicationPkOption'];
        yield ['applicationTableName'];
        yield ['applicationUpdate'];
        yield ['configurationExport'];
        yield ['configurationImport'];
        yield ['configurationPk'];
        yield ['configurationPkOption'];
        yield ['configurationTableName'];
        yield ['dcheckGet'];
        yield ['dcheckPk'];
        yield ['dcheckPkOption'];
        yield ['dcheckTableName'];
        yield ['dhostGet'];
        yield ['dhostPk'];
        yield ['dhostPkOption'];
        yield ['dhostTableName'];
        yield ['discoveryruleCopy'];
        yield ['discoveryruleCreate'];
        yield ['discoveryruleDelete'];
        yield ['discoveryruleFindInterfaceForItem'];
        yield ['discoveryruleGet'];
        yield ['discoveryrulePk'];
        yield ['discoveryrulePkOption'];
        yield ['discoveryruleSyncTemplates'];
        yield ['discoveryruleTableName'];
        yield ['discoveryruleUpdate'];
        yield ['druleCreate'];
        yield ['druleDelete'];
        yield ['druleGet'];
        yield ['drulePk'];
        yield ['drulePkOption'];
        yield ['druleTableName'];
        yield ['druleUpdate'];
        yield ['dserviceGet'];
        yield ['dservicePk'];
        yield ['dservicePkOption'];
        yield ['dserviceTableName'];
        yield ['eventAcknowledge'];
        yield ['eventGet'];
        yield ['eventPk'];
        yield ['eventPkOption'];
        yield ['eventTableName'];
        yield ['graphCreate'];
        yield ['graphDelete'];
        yield ['graphGet'];
        yield ['graphPk'];
        yield ['graphPkOption'];
        yield ['graphSyncTemplates'];
        yield ['graphTableName'];
        yield ['graphUpdate'];
        yield ['graphitemGet'];
        yield ['graphitemPk'];
        yield ['graphitemPkOption'];
        yield ['graphitemTableName'];
        yield ['graphprototypeCreate'];
        yield ['graphprototypeDelete'];
        yield ['graphprototypeGet'];
        yield ['graphprototypePk'];
        yield ['graphprototypePkOption'];
        yield ['graphprototypeSyncTemplates'];
        yield ['graphprototypeTableName'];
        yield ['graphprototypeUpdate'];
        yield ['historyGet'];
        yield ['historyPk'];
        yield ['historyPkOption'];
        yield ['historyTableName'];
        yield ['hostCreate'];
        yield ['hostDelete'];
        yield ['hostGet'];
        yield ['hostMassAdd'];
        yield ['hostMassRemove'];
        yield ['hostMassUpdate'];
        yield ['hostPk'];
        yield ['hostPkOption'];
        yield ['hostTableName'];
        yield ['hostUpdate'];
        yield ['hostgroupCreate'];
        yield ['hostgroupDelete'];
        yield ['hostgroupGet'];
        yield ['hostgroupMassAdd'];
        yield ['hostgroupMassRemove'];
        yield ['hostgroupMassUpdate'];
        yield ['hostgroupPk'];
        yield ['hostgroupPkOption'];
        yield ['hostgroupTableName'];
        yield ['hostgroupUpdate'];
        yield ['hostinterfaceCreate'];
        yield ['hostinterfaceDelete'];
        yield ['hostinterfaceGet'];
        yield ['hostinterfaceMassAdd'];
        yield ['hostinterfaceMassRemove'];
        yield ['hostinterfacePk'];
        yield ['hostinterfacePkOption'];
        yield ['hostinterfaceReplaceHostInterfaces'];
        yield ['hostinterfaceTableName'];
        yield ['hostinterfaceUpdate'];
        yield ['hostprototypeCreate'];
        yield ['hostprototypeDelete'];
        yield ['hostprototypeGet'];
        yield ['hostprototypePk'];
        yield ['hostprototypePkOption'];
        yield ['hostprototypeSyncTemplates'];
        yield ['hostprototypeTableName'];
        yield ['hostprototypeUpdate'];
        yield ['httptestCreate'];
        yield ['httptestDelete'];
        yield ['httptestGet'];
        yield ['httptestPk'];
        yield ['httptestPkOption'];
        yield ['httptestTableName'];
        yield ['httptestUpdate'];
        yield ['iconmapCreate'];
        yield ['iconmapDelete'];
        yield ['iconmapGet'];
        yield ['iconmapPk'];
        yield ['iconmapPkOption'];
        yield ['iconmapTableName'];
        yield ['iconmapUpdate'];
        yield ['imageCreate'];
        yield ['imageDelete'];
        yield ['imageGet'];
        yield ['imagePk'];
        yield ['imagePkOption'];
        yield ['imageTableName'];
        yield ['imageUpdate'];
        yield ['itemAddRelatedObjects'];
        yield ['itemCreate'];
        yield ['itemDelete'];
        yield ['itemFindInterfaceForItem'];
        yield ['itemGet'];
        yield ['itemPk'];
        yield ['itemPkOption'];
        yield ['itemSyncTemplates'];
        yield ['itemTableName'];
        yield ['itemUpdate'];
        yield ['itemValidateInventoryLinks'];
        yield ['itemprototypeAddRelatedObjects'];
        yield ['itemprototypeCreate'];
        yield ['itemprototypeDelete'];
        yield ['itemprototypeFindInterfaceForItem'];
        yield ['itemprototypeGet'];
        yield ['itemprototypePk'];
        yield ['itemprototypePkOption'];
        yield ['itemprototypeSyncTemplates'];
        yield ['itemprototypeTableName'];
        yield ['itemprototypeUpdate'];
        yield ['maintenanceCreate'];
        yield ['maintenanceDelete'];
        yield ['maintenanceGet'];
        yield ['maintenancePk'];
        yield ['maintenancePkOption'];
        yield ['maintenanceTableName'];
        yield ['maintenanceUpdate'];
        yield ['mapCreate'];
        yield ['mapDelete'];
        yield ['mapGet'];
        yield ['mapPk'];
        yield ['mapPkOption'];
        yield ['mapTableName'];
        yield ['mapUpdate'];
        yield ['mediatypeCreate'];
        yield ['mediatypeDelete'];
        yield ['mediatypeGet'];
        yield ['mediatypePk'];
        yield ['mediatypePkOption'];
        yield ['mediatypeTableName'];
        yield ['mediatypeUpdate'];
        yield ['proxyCreate'];
        yield ['proxyDelete'];
        yield ['proxyGet'];
        yield ['proxyPk'];
        yield ['proxyPkOption'];
        yield ['proxyTableName'];
        yield ['proxyUpdate'];
        yield ['screenCreate'];
        yield ['screenDelete'];
        yield ['screenGet'];
        yield ['screenPk'];
        yield ['screenPkOption'];
        yield ['screenTableName'];
        yield ['screenUpdate'];
        yield ['screenitemCreate'];
        yield ['screenitemDelete'];
        yield ['screenitemGet'];
        yield ['screenitemPk'];
        yield ['screenitemPkOption'];
        yield ['screenitemTableName'];
        yield ['screenitemUpdate'];
        yield ['screenitemUpdateByPosition'];
        yield ['scriptCreate'];
        yield ['scriptDelete'];
        yield ['scriptExecute'];
        yield ['scriptGet'];
        yield ['scriptGetScriptsByHosts'];
        yield ['scriptPk'];
        yield ['scriptPkOption'];
        yield ['scriptTableName'];
        yield ['scriptUpdate'];
        yield ['serviceAddDependencies'];
        yield ['serviceAddTimes'];
        yield ['serviceCreate'];
        yield ['serviceDelete'];
        yield ['serviceDeleteDependencies'];
        yield ['serviceDeleteTimes'];
        yield ['serviceGet'];
        yield ['serviceGetSla'];
        yield ['servicePk'];
        yield ['servicePkOption'];
        yield ['serviceTableName'];
        yield ['serviceUpdate'];
        yield ['serviceValidateAddTimes'];
        yield ['serviceValidateDelete'];
        yield ['serviceValidateUpdate'];
        yield ['templateCreate'];
        yield ['templateDelete'];
        yield ['templateGet'];
        yield ['templateMassAdd'];
        yield ['templateMassRemove'];
        yield ['templateMassUpdate'];
        yield ['templatePk'];
        yield ['templatePkOption'];
        yield ['templateTableName'];
        yield ['templateUpdate'];
        yield ['templatescreenCopy'];
        yield ['templatescreenCreate'];
        yield ['templatescreenDelete'];
        yield ['templatescreenGet'];
        yield ['templatescreenPk'];
        yield ['templatescreenPkOption'];
        yield ['templatescreenTableName'];
        yield ['templatescreenUpdate'];
        yield ['templatescreenitemGet'];
        yield ['templatescreenitemPk'];
        yield ['templatescreenitemPkOption'];
        yield ['templatescreenitemTableName'];
        yield ['trendGet'];
        yield ['trendPk'];
        yield ['trendPkOption'];
        yield ['trendTableName'];
        yield ['triggerAddDependencies'];
        yield ['triggerCreate'];
        yield ['triggerDelete'];
        yield ['triggerDeleteDependencies'];
        yield ['triggerGet'];
        yield ['triggerPk'];
        yield ['triggerPkOption'];
        yield ['triggerSyncTemplateDependencies'];
        yield ['triggerSyncTemplates'];
        yield ['triggerTableName'];
        yield ['triggerUpdate'];
        yield ['triggerprototypeCreate'];
        yield ['triggerprototypeDelete'];
        yield ['triggerprototypeGet'];
        yield ['triggerprototypePk'];
        yield ['triggerprototypePkOption'];
        yield ['triggerprototypeSyncTemplateDependencies'];
        yield ['triggerprototypeSyncTemplates'];
        yield ['triggerprototypeTableName'];
        yield ['triggerprototypeUpdate'];
        yield ['userAddMedia'];
        yield ['userCheckAuthentication'];
        yield ['userCreate'];
        yield ['userDelete'];
        yield ['userDeleteMedia'];
        yield ['userGet'];
        yield ['userLogin'];
        yield ['userLogout'];
        yield ['userPk'];
        yield ['userPkOption'];
        yield ['userTableName'];
        yield ['userUpdate'];
        yield ['userUpdateMedia'];
        yield ['userUpdateProfile'];
        yield ['usergroupCreate'];
        yield ['usergroupDelete'];
        yield ['usergroupGet'];
        yield ['usergroupMassAdd'];
        yield ['usergroupMassUpdate'];
        yield ['usergroupPk'];
        yield ['usergroupPkOption'];
        yield ['usergroupTableName'];
        yield ['usergroupUpdate'];
        yield ['usermacroCreate'];
        yield ['usermacroCreateGlobal'];
        yield ['usermacroDelete'];
        yield ['usermacroDeleteGlobal'];
        yield ['usermacroGet'];
        yield ['usermacroPk'];
        yield ['usermacroPkOption'];
        yield ['usermacroReplaceMacros'];
        yield ['usermacroTableName'];
        yield ['usermacroUpdate'];
        yield ['usermacroUpdateGlobal'];
        yield ['usermediaGet'];
        yield ['usermediaPk'];
        yield ['usermediaPkOption'];
        yield ['usermediaTableName'];
        yield ['valuemapCreate'];
        yield ['valuemapDelete'];
        yield ['valuemapGet'];
        yield ['valuemapPk'];
        yield ['valuemapPkOption'];
        yield ['valuemapTableName'];
        yield ['valuemapUpdate'];
    }

    /**
     * @dataProvider provideConstantNames
     *
     * @param string $constantName
     */
    public function testZabbixApiConstants($constantName)
    {
        $this->assertTrue(defined(sprintf('%s::%s', ZabbixApiInterface::class, $constantName)));
    }

    public function provideConstantNames()
    {
        yield ['ACCESS_DENY_OBJECT'];
        yield ['ACCESS_DENY_PAGE'];
        yield ['ACTION_DEFAULT_MSG_AUTOREG'];
        yield ['ACTION_DEFAULT_SUBJ_AUTOREG'];
        yield ['ACTION_DEFAULT_SUBJ_DISCOVERY'];
        yield ['ACTION_DEFAULT_SUBJ_TRIGGER'];
        yield ['ACTION_STATUS_DISABLED'];
        yield ['ACTION_STATUS_ENABLED'];
        yield ['ALERT_MAX_RETRIES'];
        yield ['ALERT_STATUS_FAILED'];
        yield ['ALERT_STATUS_NOT_SENT'];
        yield ['ALERT_STATUS_SENT'];
        yield ['ALERT_TYPE_COMMAND'];
        yield ['ALERT_TYPE_MESSAGE'];
        yield ['API_OUTPUT_COUNT'];
        yield ['API_OUTPUT_EXTEND'];
        yield ['AUDIT_ACTION_ADD'];
        yield ['AUDIT_ACTION_DELETE'];
        yield ['AUDIT_ACTION_DISABLE'];
        yield ['AUDIT_ACTION_ENABLE'];
        yield ['AUDIT_ACTION_LOGIN'];
        yield ['AUDIT_ACTION_LOGOUT'];
        yield ['AUDIT_ACTION_UPDATE'];
        yield ['AUDIT_RESOURCE_ACTION'];
        yield ['AUDIT_RESOURCE_APPLICATION'];
        yield ['AUDIT_RESOURCE_DISCOVERY_RULE'];
        yield ['AUDIT_RESOURCE_GRAPH'];
        yield ['AUDIT_RESOURCE_GRAPH_ELEMENT'];
        yield ['AUDIT_RESOURCE_HOST'];
        yield ['AUDIT_RESOURCE_HOST_GROUP'];
        yield ['AUDIT_RESOURCE_IMAGE'];
        yield ['AUDIT_RESOURCE_ITEM'];
        yield ['AUDIT_RESOURCE_IT_SERVICE'];
        yield ['AUDIT_RESOURCE_MACRO'];
        yield ['AUDIT_RESOURCE_MAINTENANCE'];
        yield ['AUDIT_RESOURCE_MAP'];
        yield ['AUDIT_RESOURCE_MEDIA_TYPE'];
        yield ['AUDIT_RESOURCE_PROXY'];
        yield ['AUDIT_RESOURCE_REGEXP'];
        yield ['AUDIT_RESOURCE_SCENARIO'];
        yield ['AUDIT_RESOURCE_SCREEN'];
        yield ['AUDIT_RESOURCE_SCRIPT'];
        yield ['AUDIT_RESOURCE_SLIDESHOW'];
        yield ['AUDIT_RESOURCE_TEMPLATE'];
        yield ['AUDIT_RESOURCE_TRIGGER'];
        yield ['AUDIT_RESOURCE_TRIGGER_PROTOTYPE'];
        yield ['AUDIT_RESOURCE_USER'];
        yield ['AUDIT_RESOURCE_USER_GROUP'];
        yield ['AUDIT_RESOURCE_VALUE_MAP'];
        yield ['AUDIT_RESOURCE_ZABBIX_CONFIG'];
        yield ['AVAILABILITY_REPORT_BY_HOST'];
        yield ['AVAILABILITY_REPORT_BY_TEMPLATE'];
        yield ['BR_COMPARE_VALUE_MULTIPLE_PERIODS'];
        yield ['BR_DISTRIBUTION_MULTIPLE_ITEMS'];
        yield ['BR_DISTRIBUTION_MULTIPLE_PERIODS'];
        yield ['CALC_FNC_ALL'];
        yield ['CALC_FNC_AVG'];
        yield ['CALC_FNC_LST'];
        yield ['CALC_FNC_MAX'];
        yield ['CALC_FNC_MIN'];
        yield ['CONDITION_EVAL_TYPE_AND'];
        yield ['CONDITION_EVAL_TYPE_AND_OR'];
        yield ['CONDITION_EVAL_TYPE_EXPRESSION'];
        yield ['CONDITION_EVAL_TYPE_OR'];
        yield ['CONDITION_OPERATOR_EQUAL'];
        yield ['CONDITION_OPERATOR_IN'];
        yield ['CONDITION_OPERATOR_LESS_EQUAL'];
        yield ['CONDITION_OPERATOR_LIKE'];
        yield ['CONDITION_OPERATOR_MORE_EQUAL'];
        yield ['CONDITION_OPERATOR_NOT_EQUAL'];
        yield ['CONDITION_OPERATOR_NOT_IN'];
        yield ['CONDITION_OPERATOR_NOT_LIKE'];
        yield ['CONDITION_OPERATOR_REGEXP'];
        yield ['CONDITION_TYPE_APPLICATION'];
        yield ['CONDITION_TYPE_DCHECK'];
        yield ['CONDITION_TYPE_DHOST_IP'];
        yield ['CONDITION_TYPE_DOBJECT'];
        yield ['CONDITION_TYPE_DRULE'];
        yield ['CONDITION_TYPE_DSERVICE_PORT'];
        yield ['CONDITION_TYPE_DSERVICE_TYPE'];
        yield ['CONDITION_TYPE_DSTATUS'];
        yield ['CONDITION_TYPE_DUPTIME'];
        yield ['CONDITION_TYPE_DVALUE'];
        yield ['CONDITION_TYPE_EVENT_ACKNOWLEDGED'];
        yield ['CONDITION_TYPE_EVENT_TYPE'];
        yield ['CONDITION_TYPE_HOST'];
        yield ['CONDITION_TYPE_HOST_GROUP'];
        yield ['CONDITION_TYPE_HOST_METADATA'];
        yield ['CONDITION_TYPE_HOST_NAME'];
        yield ['CONDITION_TYPE_MAINTENANCE'];
        yield ['CONDITION_TYPE_PROXY'];
        yield ['CONDITION_TYPE_TEMPLATE'];
        yield ['CONDITION_TYPE_TIME_PERIOD'];
        yield ['CONDITION_TYPE_TRIGGER'];
        yield ['CONDITION_TYPE_TRIGGER_NAME'];
        yield ['CONDITION_TYPE_TRIGGER_SEVERITY'];
        yield ['CONDITION_TYPE_TRIGGER_VALUE'];
        yield ['COPY_TYPE_TO_HOST'];
        yield ['COPY_TYPE_TO_HOST_GROUP'];
        yield ['COPY_TYPE_TO_TEMPLATE'];
        yield ['DATE_FORMAT_CONTEXT'];
        yield ['DATE_TIME_FORMAT_SECONDS_XML'];
        yield ['DAY_IN_YEAR'];
        yield ['DB_ID'];
        yield ['DEFAULT_LATEST_ISSUES_CNT'];
        yield ['DHOST_STATUS_ACTIVE'];
        yield ['DHOST_STATUS_DISABLED'];
        yield ['DOBJECT_STATUS_DISCOVER'];
        yield ['DOBJECT_STATUS_DOWN'];
        yield ['DOBJECT_STATUS_LOST'];
        yield ['DOBJECT_STATUS_UP'];
        yield ['DRULE_STATUS_ACTIVE'];
        yield ['DRULE_STATUS_DISABLED'];
        yield ['DSVC_STATUS_ACTIVE'];
        yield ['DSVC_STATUS_DISABLED'];
        yield ['EVENTS_OPTION_ALL'];
        yield ['EVENTS_OPTION_NOEVENT'];
        yield ['EVENTS_OPTION_NOT_ACK'];
        yield ['EVENT_ACKNOWLEDGED'];
        yield ['EVENT_ACK_DISABLED'];
        yield ['EVENT_ACK_ENABLED'];
        yield ['EVENT_NOT_ACKNOWLEDGED'];
        yield ['EVENT_OBJECT_AUTOREGHOST'];
        yield ['EVENT_OBJECT_DHOST'];
        yield ['EVENT_OBJECT_DSERVICE'];
        yield ['EVENT_OBJECT_ITEM'];
        yield ['EVENT_OBJECT_LLDRULE'];
        yield ['EVENT_OBJECT_TRIGGER'];
        yield ['EVENT_SOURCE_AUTO_REGISTRATION'];
        yield ['EVENT_SOURCE_DISCOVERY'];
        yield ['EVENT_SOURCE_INTERNAL'];
        yield ['EVENT_SOURCE_TRIGGERS'];
        yield ['EVENT_TYPE_ITEM_NORMAL'];
        yield ['EVENT_TYPE_ITEM_NOTSUPPORTED'];
        yield ['EVENT_TYPE_LLDRULE_NORMAL'];
        yield ['EVENT_TYPE_LLDRULE_NOTSUPPORTED'];
        yield ['EVENT_TYPE_TRIGGER_NORMAL'];
        yield ['EVENT_TYPE_TRIGGER_UNKNOWN'];
        yield ['EXPRESSION_FUNCTION_UNKNOWN'];
        yield ['EXPRESSION_HOST_ITEM_UNKNOWN'];
        yield ['EXPRESSION_HOST_UNKNOWN'];
        yield ['EXPRESSION_NOT_A_MACRO_ERROR'];
        yield ['EXPRESSION_TYPE_ANY_INCLUDED'];
        yield ['EXPRESSION_TYPE_FALSE'];
        yield ['EXPRESSION_TYPE_INCLUDED'];
        yield ['EXPRESSION_TYPE_NOT_INCLUDED'];
        yield ['EXPRESSION_TYPE_TRUE'];
        yield ['EXTACK_OPTION_ALL'];
        yield ['EXTACK_OPTION_BOTH'];
        yield ['EXTACK_OPTION_UNACK'];
        yield ['EZ_TEXTING_LIMIT_CANADA'];
        yield ['EZ_TEXTING_LIMIT_USA'];
        yield ['FILTER_TASK_HIDE'];
        yield ['FILTER_TASK_INVERT_MARK'];
        yield ['FILTER_TASK_MARK'];
        yield ['FILTER_TASK_SHOW'];
        yield ['GRAPH_3D_ANGLE'];
        yield ['GRAPH_ITEM_DRAWTYPE_BOLD_DOT'];
        yield ['GRAPH_ITEM_DRAWTYPE_BOLD_LINE'];
        yield ['GRAPH_ITEM_DRAWTYPE_DASHED_LINE'];
        yield ['GRAPH_ITEM_DRAWTYPE_DOT'];
        yield ['GRAPH_ITEM_DRAWTYPE_FILLED_REGION'];
        yield ['GRAPH_ITEM_DRAWTYPE_GRADIENT_LINE'];
        yield ['GRAPH_ITEM_DRAWTYPE_LINE'];
        yield ['GRAPH_ITEM_SIMPLE'];
        yield ['GRAPH_ITEM_SUM'];
        yield ['GRAPH_STACKED_ALFA'];
        yield ['GRAPH_TRIGGER_LINE_OPPOSITE_COLOR'];
        yield ['GRAPH_TYPE_3D'];
        yield ['GRAPH_TYPE_3D_EXPLODED'];
        yield ['GRAPH_TYPE_BAR'];
        yield ['GRAPH_TYPE_BAR_STACKED'];
        yield ['GRAPH_TYPE_COLUMN'];
        yield ['GRAPH_TYPE_COLUMN_STACKED'];
        yield ['GRAPH_TYPE_EXPLODED'];
        yield ['GRAPH_TYPE_NORMAL'];
        yield ['GRAPH_TYPE_PIE'];
        yield ['GRAPH_TYPE_STACKED'];
        yield ['GRAPH_YAXIS_SIDE_DEFAULT'];
        yield ['GRAPH_YAXIS_SIDE_LEFT'];
        yield ['GRAPH_YAXIS_SIDE_RIGHT'];
        yield ['GRAPH_YAXIS_TYPE_CALCULATED'];
        yield ['GRAPH_YAXIS_TYPE_FIXED'];
        yield ['GRAPH_YAXIS_TYPE_ITEM_VALUE'];
        yield ['GRAPH_ZERO_LINE_COLOR_LEFT'];
        yield ['GRAPH_ZERO_LINE_COLOR_RIGHT'];
        yield ['GROUP_DEBUG_MODE_DISABLED'];
        yield ['GROUP_DEBUG_MODE_ENABLED'];
        yield ['GROUP_GUI_ACCESS_DISABLED'];
        yield ['GROUP_GUI_ACCESS_INTERNAL'];
        yield ['GROUP_GUI_ACCESS_SYSTEM'];
        yield ['GROUP_STATUS_DISABLED'];
        yield ['GROUP_STATUS_ENABLED'];
        yield ['HALIGN_CENTER'];
        yield ['HALIGN_DEFAULT'];
        yield ['HALIGN_LEFT'];
        yield ['HALIGN_RIGHT'];
        yield ['HISTORY_BATCH_GRAPH'];
        yield ['HISTORY_GRAPH'];
        yield ['HISTORY_LATEST'];
        yield ['HISTORY_VALUES'];
        yield ['HOST_AVAILABLE_FALSE'];
        yield ['HOST_AVAILABLE_TRUE'];
        yield ['HOST_AVAILABLE_UNKNOWN'];
        yield ['HOST_ENCRYPTION_CERTIFICATE'];
        yield ['HOST_ENCRYPTION_NONE'];
        yield ['HOST_ENCRYPTION_PSK'];
        yield ['HOST_INVENTORY_AUTOMATIC'];
        yield ['HOST_INVENTORY_DISABLED'];
        yield ['HOST_INVENTORY_MANUAL'];
        yield ['HOST_MAINTENANCE_STATUS_OFF'];
        yield ['HOST_MAINTENANCE_STATUS_ON'];
        yield ['HOST_STATUS_MONITORED'];
        yield ['HOST_STATUS_NOT_MONITORED'];
        yield ['HOST_STATUS_PROXY_ACTIVE'];
        yield ['HOST_STATUS_PROXY_PASSIVE'];
        yield ['HOST_STATUS_TEMPLATE'];
        yield ['HTTPSTEP_ITEM_TYPE_IN'];
        yield ['HTTPSTEP_ITEM_TYPE_LASTERROR'];
        yield ['HTTPSTEP_ITEM_TYPE_LASTSTEP'];
        yield ['HTTPSTEP_ITEM_TYPE_RSPCODE'];
        yield ['HTTPSTEP_ITEM_TYPE_TIME'];
        yield ['HTTPTEST_AUTH_BASIC'];
        yield ['HTTPTEST_AUTH_NONE'];
        yield ['HTTPTEST_AUTH_NTLM'];
        yield ['HTTPTEST_STATUS_ACTIVE'];
        yield ['HTTPTEST_STATUS_DISABLED'];
        yield ['HTTPTEST_STEP_FOLLOW_REDIRECTS_OFF'];
        yield ['HTTPTEST_STEP_FOLLOW_REDIRECTS_ON'];
        yield ['HTTPTEST_STEP_RETRIEVE_MODE_CONTENT'];
        yield ['HTTPTEST_STEP_RETRIEVE_MODE_HEADERS'];
        yield ['HTTPTEST_VERIFY_HOST_OFF'];
        yield ['HTTPTEST_VERIFY_HOST_ON'];
        yield ['HTTPTEST_VERIFY_PEER_OFF'];
        yield ['HTTPTEST_VERIFY_PEER_ON'];
        yield ['IMAGE_FORMAT_JPEG'];
        yield ['IMAGE_FORMAT_PNG'];
        yield ['IMAGE_FORMAT_TEXT'];
        yield ['IMAGE_TYPE_BACKGROUND'];
        yield ['IMAGE_TYPE_ICON'];
        yield ['IM_ESTABLISHED'];
        yield ['IM_FORCED'];
        yield ['IM_TREE'];
        yield ['INTERFACE_PRIMARY'];
        yield ['INTERFACE_SECONDARY'];
        yield ['INTERFACE_TYPE_AGENT'];
        yield ['INTERFACE_TYPE_ANY'];
        yield ['INTERFACE_TYPE_IPMI'];
        yield ['INTERFACE_TYPE_JMX'];
        yield ['INTERFACE_TYPE_SNMP'];
        yield ['INTERFACE_TYPE_UNKNOWN'];
        yield ['INTERFACE_USE_DNS'];
        yield ['INTERFACE_USE_IP'];
        yield ['IPMI_AUTHTYPE_DEFAULT'];
        yield ['IPMI_AUTHTYPE_MD2'];
        yield ['IPMI_AUTHTYPE_MD5'];
        yield ['IPMI_AUTHTYPE_NONE'];
        yield ['IPMI_AUTHTYPE_OEM'];
        yield ['IPMI_AUTHTYPE_RMCP_PLUS'];
        yield ['IPMI_AUTHTYPE_STRAIGHT'];
        yield ['IPMI_PRIVILEGE_ADMIN'];
        yield ['IPMI_PRIVILEGE_CALLBACK'];
        yield ['IPMI_PRIVILEGE_OEM'];
        yield ['IPMI_PRIVILEGE_OPERATOR'];
        yield ['IPMI_PRIVILEGE_USER'];
        yield ['ITEM_AUTHPROTOCOL_MD5'];
        yield ['ITEM_AUTHPROTOCOL_SHA'];
        yield ['ITEM_AUTHTYPE_PASSWORD'];
        yield ['ITEM_AUTHTYPE_PUBLICKEY'];
        yield ['ITEM_CONVERT_NO_UNITS'];
        yield ['ITEM_CONVERT_WITH_UNITS'];
        yield ['ITEM_DATA_TYPE_BOOLEAN'];
        yield ['ITEM_DATA_TYPE_DECIMAL'];
        yield ['ITEM_DATA_TYPE_HEXADECIMAL'];
        yield ['ITEM_DATA_TYPE_OCTAL'];
        yield ['ITEM_DELAY_FLEX_TYPE_FLEXIBLE'];
        yield ['ITEM_DELAY_FLEX_TYPE_SCHEDULING'];
        yield ['ITEM_LOGTYPE_CRITICAL'];
        yield ['ITEM_LOGTYPE_ERROR'];
        yield ['ITEM_LOGTYPE_FAILURE_AUDIT'];
        yield ['ITEM_LOGTYPE_INFORMATION'];
        yield ['ITEM_LOGTYPE_SUCCESS_AUDIT'];
        yield ['ITEM_LOGTYPE_VERBOSE'];
        yield ['ITEM_LOGTYPE_WARNING'];
        yield ['ITEM_PRIVPROTOCOL_AES'];
        yield ['ITEM_PRIVPROTOCOL_DES'];
        yield ['ITEM_SNMPV3_SECURITYLEVEL_AUTHNOPRIV'];
        yield ['ITEM_SNMPV3_SECURITYLEVEL_AUTHPRIV'];
        yield ['ITEM_SNMPV3_SECURITYLEVEL_NOAUTHNOPRIV'];
        yield ['ITEM_STATE_NORMAL'];
        yield ['ITEM_STATE_NOTSUPPORTED'];
        yield ['ITEM_STATUS_ACTIVE'];
        yield ['ITEM_STATUS_DISABLED'];
        yield ['ITEM_STATUS_NOTSUPPORTED'];
        yield ['ITEM_TYPE_AGGREGATE'];
        yield ['ITEM_TYPE_CALCULATED'];
        yield ['ITEM_TYPE_DB_MONITOR'];
        yield ['ITEM_TYPE_EXTERNAL'];
        yield ['ITEM_TYPE_HTTPTEST'];
        yield ['ITEM_TYPE_INTERNAL'];
        yield ['ITEM_TYPE_IPMI'];
        yield ['ITEM_TYPE_JMX'];
        yield ['ITEM_TYPE_SIMPLE'];
        yield ['ITEM_TYPE_SNMPTRAP'];
        yield ['ITEM_TYPE_SNMPV1'];
        yield ['ITEM_TYPE_SNMPV2C'];
        yield ['ITEM_TYPE_SNMPV3'];
        yield ['ITEM_TYPE_SSH'];
        yield ['ITEM_TYPE_TELNET'];
        yield ['ITEM_TYPE_TRAPPER'];
        yield ['ITEM_TYPE_ZABBIX'];
        yield ['ITEM_TYPE_ZABBIX_ACTIVE'];
        yield ['ITEM_VALUE_TYPE_FLOAT'];
        yield ['ITEM_VALUE_TYPE_LOG'];
        yield ['ITEM_VALUE_TYPE_STR'];
        yield ['ITEM_VALUE_TYPE_TEXT'];
        yield ['ITEM_VALUE_TYPE_UINT64'];
        yield ['LIBXML_IMPORT_FLAGS'];
        yield ['LINE_TYPE_BOLD'];
        yield ['LINE_TYPE_NORMAL'];
        yield ['MACRO_TYPE_BOTH'];
        yield ['MACRO_TYPE_HOSTMACRO'];
        yield ['MACRO_TYPE_INHERITED'];
        yield ['MAINTENANCE_STATUS_ACTIVE'];
        yield ['MAINTENANCE_STATUS_APPROACH'];
        yield ['MAINTENANCE_STATUS_EXPIRED'];
        yield ['MAINTENANCE_TYPE_NODATA'];
        yield ['MAINTENANCE_TYPE_NORMAL'];
        yield ['MAP_DEFAULT_ICON'];
        yield ['MAP_LABEL_LOC_BOTTOM'];
        yield ['MAP_LABEL_LOC_DEFAULT'];
        yield ['MAP_LABEL_LOC_LEFT'];
        yield ['MAP_LABEL_LOC_RIGHT'];
        yield ['MAP_LABEL_LOC_TOP'];
        yield ['MAP_LABEL_TYPE_CUSTOM'];
        yield ['MAP_LABEL_TYPE_IP'];
        yield ['MAP_LABEL_TYPE_LABEL'];
        yield ['MAP_LABEL_TYPE_NAME'];
        yield ['MAP_LABEL_TYPE_NOTHING'];
        yield ['MAP_LABEL_TYPE_STATUS'];
        yield ['MAP_LINK_DRAWTYPE_BOLD_LINE'];
        yield ['MAP_LINK_DRAWTYPE_DASHED_LINE'];
        yield ['MAP_LINK_DRAWTYPE_DOT'];
        yield ['MAP_LINK_DRAWTYPE_LINE'];
        yield ['MARK_COLOR_BLUE'];
        yield ['MARK_COLOR_GREEN'];
        yield ['MARK_COLOR_RED'];
        yield ['MEDIA_STATUS_ACTIVE'];
        yield ['MEDIA_STATUS_DISABLED'];
        yield ['MEDIA_TYPE_EMAIL'];
        yield ['MEDIA_TYPE_EXEC'];
        yield ['MEDIA_TYPE_EZ_TEXTING'];
        yield ['MEDIA_TYPE_JABBER'];
        yield ['MEDIA_TYPE_SMS'];
        yield ['MEDIA_TYPE_STATUS_ACTIVE'];
        yield ['MEDIA_TYPE_STATUS_DISABLED'];
        yield ['NAME_DELIMITER'];
        yield ['NOT_EMPTY'];
        yield ['NOT_ZERO'];
        yield ['OPERATION_TYPE_COMMAND'];
        yield ['OPERATION_TYPE_GROUP_ADD'];
        yield ['OPERATION_TYPE_GROUP_REMOVE'];
        yield ['OPERATION_TYPE_HOST_ADD'];
        yield ['OPERATION_TYPE_HOST_DISABLE'];
        yield ['OPERATION_TYPE_HOST_ENABLE'];
        yield ['OPERATION_TYPE_HOST_INVENTORY'];
        yield ['OPERATION_TYPE_HOST_REMOVE'];
        yield ['OPERATION_TYPE_MESSAGE'];
        yield ['OPERATION_TYPE_TEMPLATE_ADD'];
        yield ['OPERATION_TYPE_TEMPLATE_REMOVE'];
        yield ['O_MAND'];
        yield ['O_NO'];
        yield ['O_OPT'];
        yield ['PAGE_TYPE_CSS'];
        yield ['PAGE_TYPE_CSV'];
        yield ['PAGE_TYPE_HTML'];
        yield ['PAGE_TYPE_HTML_BLOCK'];
        yield ['PAGE_TYPE_IMAGE'];
        yield ['PAGE_TYPE_JS'];
        yield ['PAGE_TYPE_JSON'];
        yield ['PAGE_TYPE_JSON_RPC'];
        yield ['PAGE_TYPE_TEXT'];
        yield ['PAGE_TYPE_TEXT_FILE'];
        yield ['PAGE_TYPE_TEXT_RETURN_JSON'];
        yield ['PAGE_TYPE_XML'];
        yield ['PARAM_TYPE_COUNTS'];
        yield ['PARAM_TYPE_TIME'];
        yield ['PERM_DENY'];
        yield ['PERM_READ'];
        yield ['PERM_READ_WRITE'];
        yield ['PRIVATE_SHARING'];
        yield ['PROFILE_TYPE_ID'];
        yield ['PROFILE_TYPE_INT'];
        yield ['PROFILE_TYPE_STR'];
        yield ['PSK_MIN_LEN'];
        yield ['PUBLIC_SHARING'];
        yield ['P_ACT'];
        yield ['P_NO_TRIM'];
        yield ['P_NZERO'];
        yield ['P_SYS'];
        yield ['P_UNSET_EMPTY'];
        yield ['QUEUE_DETAILS'];
        yield ['QUEUE_DETAIL_ITEM_COUNT'];
        yield ['QUEUE_OVERVIEW'];
        yield ['QUEUE_OVERVIEW_BY_PROXY'];
        yield ['REPORT_PERIOD_CURRENT_MONTH'];
        yield ['REPORT_PERIOD_CURRENT_WEEK'];
        yield ['REPORT_PERIOD_CURRENT_YEAR'];
        yield ['REPORT_PERIOD_LAST_MONTH'];
        yield ['REPORT_PERIOD_LAST_WEEK'];
        yield ['REPORT_PERIOD_LAST_YEAR'];
        yield ['REPORT_PERIOD_TODAY'];
        yield ['REPORT_PERIOD_YESTERDAY'];
        yield ['SCREEN_DYNAMIC_ITEM'];
        yield ['SCREEN_MODE_EDIT'];
        yield ['SCREEN_MODE_JS'];
        yield ['SCREEN_MODE_PREVIEW'];
        yield ['SCREEN_MODE_SLIDESHOW'];
        yield ['SCREEN_REFRESH_RESPONSIVENESS'];
        yield ['SCREEN_REFRESH_TIMEOUT'];
        yield ['SCREEN_RESOURCE_ACTIONS'];
        yield ['SCREEN_RESOURCE_CHART'];
        yield ['SCREEN_RESOURCE_CLOCK'];
        yield ['SCREEN_RESOURCE_DATA_OVERVIEW'];
        yield ['SCREEN_RESOURCE_EVENTS'];
        yield ['SCREEN_RESOURCE_GRAPH'];
        yield ['SCREEN_RESOURCE_HISTORY'];
        yield ['SCREEN_RESOURCE_HOSTGROUP_TRIGGERS'];
        yield ['SCREEN_RESOURCE_HOSTS_INFO'];
        yield ['SCREEN_RESOURCE_HOST_TRIGGERS'];
        yield ['SCREEN_RESOURCE_HTTPTEST_DETAILS'];
        yield ['SCREEN_RESOURCE_LLD_GRAPH'];
        yield ['SCREEN_RESOURCE_LLD_SIMPLE_GRAPH'];
        yield ['SCREEN_RESOURCE_MAP'];
        yield ['SCREEN_RESOURCE_PLAIN_TEXT'];
        yield ['SCREEN_RESOURCE_SCREEN'];
        yield ['SCREEN_RESOURCE_SERVER_INFO'];
        yield ['SCREEN_RESOURCE_SIMPLE_GRAPH'];
        yield ['SCREEN_RESOURCE_SYSTEM_STATUS'];
        yield ['SCREEN_RESOURCE_TRIGGERS_INFO'];
        yield ['SCREEN_RESOURCE_TRIGGERS_OVERVIEW'];
        yield ['SCREEN_RESOURCE_URL'];
        yield ['SCREEN_SIMPLE_ITEM'];
        yield ['SCREEN_SORT_TRIGGERS_DATE_DESC'];
        yield ['SCREEN_SORT_TRIGGERS_HOST_NAME_ASC'];
        yield ['SCREEN_SORT_TRIGGERS_RECIPIENT_ASC'];
        yield ['SCREEN_SORT_TRIGGERS_RECIPIENT_DESC'];
        yield ['SCREEN_SORT_TRIGGERS_SEVERITY_DESC'];
        yield ['SCREEN_SORT_TRIGGERS_STATUS_ASC'];
        yield ['SCREEN_SORT_TRIGGERS_STATUS_DESC'];
        yield ['SCREEN_SORT_TRIGGERS_TIME_ASC'];
        yield ['SCREEN_SORT_TRIGGERS_TIME_DESC'];
        yield ['SCREEN_SORT_TRIGGERS_TYPE_ASC'];
        yield ['SCREEN_SORT_TRIGGERS_TYPE_DESC'];
        yield ['SCREEN_SURROGATE_MAX_COLUMNS_DEFAULT'];
        yield ['SCREEN_SURROGATE_MAX_COLUMNS_MAX'];
        yield ['SCREEN_SURROGATE_MAX_COLUMNS_MIN'];
        yield ['SEC_PER_DAY'];
        yield ['SEC_PER_HOUR'];
        yield ['SEC_PER_MIN'];
        yield ['SEC_PER_MONTH'];
        yield ['SEC_PER_WEEK'];
        yield ['SEC_PER_YEAR'];
        yield ['SERVER_CHECK_INTERVAL'];
        yield ['SERVICE_ALGORITHM_MAX'];
        yield ['SERVICE_ALGORITHM_MIN'];
        yield ['SERVICE_ALGORITHM_NONE'];
        yield ['SERVICE_SHOW_SLA_OFF'];
        yield ['SERVICE_SHOW_SLA_ON'];
        yield ['SERVICE_SLA'];
        yield ['SERVICE_STATUS_OK'];
        yield ['SERVICE_TIME_TYPE_DOWNTIME'];
        yield ['SERVICE_TIME_TYPE_ONETIME_DOWNTIME'];
        yield ['SERVICE_TIME_TYPE_UPTIME'];
        yield ['SMTP_AUTHENTICATION_NONE'];
        yield ['SMTP_AUTHENTICATION_NORMAL'];
        yield ['SMTP_CONNECTION_SECURITY_NONE'];
        yield ['SMTP_CONNECTION_SECURITY_SSL_TLS'];
        yield ['SMTP_CONNECTION_SECURITY_STARTTLS'];
        yield ['SNMP_BULK_DISABLED'];
        yield ['SNMP_BULK_ENABLED'];
        yield ['SPACE'];
        yield ['STYLE_HORIZONTAL'];
        yield ['STYLE_LEFT'];
        yield ['STYLE_TOP'];
        yield ['STYLE_VERTICAL'];
        yield ['SVC_AGENT'];
        yield ['SVC_FTP'];
        yield ['SVC_HTTP'];
        yield ['SVC_HTTPS'];
        yield ['SVC_ICMPPING'];
        yield ['SVC_IMAP'];
        yield ['SVC_LDAP'];
        yield ['SVC_NNTP'];
        yield ['SVC_POP'];
        yield ['SVC_SMTP'];
        yield ['SVC_SNMPv1'];
        yield ['SVC_SNMPv2c'];
        yield ['SVC_SNMPv3'];
        yield ['SVC_SSH'];
        yield ['SVC_TCP'];
        yield ['SVC_TELNET'];
        yield ['SYSMAP_ELEMENT_AREA_TYPE_CUSTOM'];
        yield ['SYSMAP_ELEMENT_AREA_TYPE_FIT'];
        yield ['SYSMAP_ELEMENT_AREA_VIEWTYPE_GRID'];
        yield ['SYSMAP_ELEMENT_ICON_DISABLED'];
        yield ['SYSMAP_ELEMENT_ICON_MAINTENANCE'];
        yield ['SYSMAP_ELEMENT_ICON_OFF'];
        yield ['SYSMAP_ELEMENT_ICON_ON'];
        yield ['SYSMAP_ELEMENT_SUBTYPE_HOST_GROUP'];
        yield ['SYSMAP_ELEMENT_SUBTYPE_HOST_GROUP_ELEMENTS'];
        yield ['SYSMAP_ELEMENT_TYPE_HOST'];
        yield ['SYSMAP_ELEMENT_TYPE_HOST_GROUP'];
        yield ['SYSMAP_ELEMENT_TYPE_IMAGE'];
        yield ['SYSMAP_ELEMENT_TYPE_MAP'];
        yield ['SYSMAP_ELEMENT_TYPE_TRIGGER'];
        yield ['SYSMAP_ELEMENT_USE_ICONMAP_OFF'];
        yield ['SYSMAP_ELEMENT_USE_ICONMAP_ON'];
        yield ['SYSMAP_EXPAND_MACROS_OFF'];
        yield ['SYSMAP_EXPAND_MACROS_ON'];
        yield ['SYSMAP_GRID_ALIGN_OFF'];
        yield ['SYSMAP_GRID_ALIGN_ON'];
        yield ['SYSMAP_GRID_SHOW_OFF'];
        yield ['SYSMAP_GRID_SHOW_ON'];
        yield ['SYSMAP_HIGHLIGHT_OFF'];
        yield ['SYSMAP_HIGHLIGHT_ON'];
        yield ['SYSMAP_LABEL_ADVANCED_OFF'];
        yield ['SYSMAP_LABEL_ADVANCED_ON'];
        yield ['THEME_DEFAULT'];
        yield ['TIMEPERIOD_TYPE_DAILY'];
        yield ['TIMEPERIOD_TYPE_HOURLY'];
        yield ['TIMEPERIOD_TYPE_MONTHLY'];
        yield ['TIMEPERIOD_TYPE_ONETIME'];
        yield ['TIMEPERIOD_TYPE_WEEKLY'];
        yield ['TIMEPERIOD_TYPE_YEARLY'];
        yield ['TIMESTAMP_FORMAT'];
        yield ['TIMESTAMP_FORMAT_ZERO_TIME'];
        yield ['TIME_TYPE_HOST'];
        yield ['TIME_TYPE_LOCAL'];
        yield ['TIME_TYPE_SERVER'];
        yield ['TRIGGERS_OPTION_ALL'];
        yield ['TRIGGERS_OPTION_IN_PROBLEM'];
        yield ['TRIGGERS_OPTION_RECENT_PROBLEM'];
        yield ['TRIGGER_MULT_EVENT_DISABLED'];
        yield ['TRIGGER_MULT_EVENT_ENABLED'];
        yield ['TRIGGER_SEVERITY_AVERAGE'];
        yield ['TRIGGER_SEVERITY_COUNT'];
        yield ['TRIGGER_SEVERITY_DISASTER'];
        yield ['TRIGGER_SEVERITY_HIGH'];
        yield ['TRIGGER_SEVERITY_INFORMATION'];
        yield ['TRIGGER_SEVERITY_NOT_CLASSIFIED'];
        yield ['TRIGGER_SEVERITY_WARNING'];
        yield ['TRIGGER_STATE_NORMAL'];
        yield ['TRIGGER_STATE_UNKNOWN'];
        yield ['TRIGGER_STATUS_DISABLED'];
        yield ['TRIGGER_STATUS_ENABLED'];
        yield ['TRIGGER_VALUE_FALSE'];
        yield ['TRIGGER_VALUE_TRUE'];
        yield ['T_ZBX_CLR'];
        yield ['T_ZBX_DBL'];
        yield ['T_ZBX_DBL_BIG'];
        yield ['T_ZBX_DBL_STR'];
        yield ['T_ZBX_INT'];
        yield ['T_ZBX_STR'];
        yield ['T_ZBX_TP'];
        yield ['UNKNOWN_VALUE'];
        yield ['USER_TYPE_SUPER_ADMIN'];
        yield ['USER_TYPE_ZABBIX_ADMIN'];
        yield ['USER_TYPE_ZABBIX_USER'];
        yield ['VALIGN_BOTTOM'];
        yield ['VALIGN_DEFAULT'];
        yield ['VALIGN_MIDDLE'];
        yield ['VALIGN_TOP'];
        yield ['WIDGET_DISCOVERY_STATUS'];
        yield ['WIDGET_FAVOURITE_GRAPHS'];
        yield ['WIDGET_FAVOURITE_MAPS'];
        yield ['WIDGET_FAVOURITE_SCREENS'];
        yield ['WIDGET_HAT_EVENTACK'];
        yield ['WIDGET_HAT_EVENTACTIONMCMDS'];
        yield ['WIDGET_HAT_EVENTACTIONMSGS'];
        yield ['WIDGET_HAT_EVENTDETAILS'];
        yield ['WIDGET_HAT_EVENTLIST'];
        yield ['WIDGET_HAT_TRIGGERDETAILS'];
        yield ['WIDGET_HOST_STATUS'];
        yield ['WIDGET_LAST_ISSUES'];
        yield ['WIDGET_SEARCH_HOSTGROUP'];
        yield ['WIDGET_SEARCH_HOSTS'];
        yield ['WIDGET_SEARCH_TEMPLATES'];
        yield ['WIDGET_SLIDESHOW'];
        yield ['WIDGET_SYSTEM_STATUS'];
        yield ['WIDGET_WEB_OVERVIEW'];
        yield ['WIDGET_ZABBIX_STATUS'];
        yield ['XML_ARRAY'];
        yield ['XML_INDEXED_ARRAY'];
        yield ['XML_REQUIRED'];
        yield ['XML_STRING'];
        yield ['XML_TAG_DEPENDENCY'];
        yield ['XML_TAG_GRAPH'];
        yield ['XML_TAG_GRAPH_ELEMENT'];
        yield ['XML_TAG_HOST'];
        yield ['XML_TAG_HOSTINVENTORY'];
        yield ['XML_TAG_ITEM'];
        yield ['XML_TAG_MACRO'];
        yield ['XML_TAG_TRIGGER'];
        yield ['ZABBIX_API_VERSION'];
        yield ['ZABBIX_COPYRIGHT_FROM'];
        yield ['ZABBIX_COPYRIGHT_TO'];
        yield ['ZABBIX_DB_VERSION'];
        yield ['ZABBIX_EXPORT_VERSION'];
        yield ['ZABBIX_HOMEPAGE'];
        yield ['ZABBIX_VERSION'];
        yield ['ZBX_ACKNOWLEDGE_ALL'];
        yield ['ZBX_ACKNOWLEDGE_PROBLEM'];
        yield ['ZBX_ACKNOWLEDGE_SELECTED'];
        yield ['ZBX_ACK_STS_ANY'];
        yield ['ZBX_ACK_STS_WITH_LAST_UNACK'];
        yield ['ZBX_ACK_STS_WITH_UNACK'];
        yield ['ZBX_AGENT_OTHER'];
        yield ['ZBX_API_ERROR_INTERNAL'];
        yield ['ZBX_API_ERROR_NO_AUTH'];
        yield ['ZBX_API_ERROR_NO_METHOD'];
        yield ['ZBX_API_ERROR_PARAMETERS'];
        yield ['ZBX_API_ERROR_PERMISSIONS'];
        yield ['ZBX_AUTH_HTTP'];
        yield ['ZBX_AUTH_INTERNAL'];
        yield ['ZBX_AUTH_LDAP'];
        yield ['ZBX_BYTE_SUFFIXES'];
        yield ['ZBX_DB_DB2'];
        yield ['ZBX_DB_MAX_ID'];
        yield ['ZBX_DB_MAX_INSERTS'];
        yield ['ZBX_DB_MYSQL'];
        yield ['ZBX_DB_ORACLE'];
        yield ['ZBX_DB_POSTGRESQL'];
        yield ['ZBX_DB_SQLITE3'];
        yield ['ZBX_DEFAULT_AGENT'];
        yield ['ZBX_DEFAULT_IMPORT_HOST_GROUP'];
        yield ['ZBX_DEFAULT_INTERVAL'];
        yield ['ZBX_DEFAULT_KEY_DB_MONITOR'];
        yield ['ZBX_DEFAULT_KEY_DB_MONITOR_DISCOVERY'];
        yield ['ZBX_DEFAULT_KEY_JMX'];
        yield ['ZBX_DEFAULT_KEY_SSH'];
        yield ['ZBX_DEFAULT_KEY_TELNET'];
        yield ['ZBX_DEFAULT_THEME'];
        yield ['ZBX_DEFAULT_URL'];
        yield ['ZBX_DISCOVERER_IPRANGE_LIMIT'];
        yield ['ZBX_DROPDOWN_FIRST_ALL'];
        yield ['ZBX_DROPDOWN_FIRST_NONE'];
        yield ['ZBX_FLAG_DISCOVERY_CREATED'];
        yield ['ZBX_FLAG_DISCOVERY_NORMAL'];
        yield ['ZBX_FLAG_DISCOVERY_PROTOTYPE'];
        yield ['ZBX_FLAG_DISCOVERY_RULE'];
        yield ['ZBX_FONT_NAME'];
        yield ['ZBX_GRAPH_FONT_NAME'];
        yield ['ZBX_GRAPH_LEGEND_HEIGHT'];
        yield ['ZBX_GRAPH_MAX_SKIP_CELL'];
        yield ['ZBX_GRAPH_MAX_SKIP_DELAY'];
        yield ['ZBX_GUEST_USER'];
        yield ['ZBX_HAVE_IPV6'];
        yield ['ZBX_HISTORY_PERIOD'];
        yield ['ZBX_HOST_INTERFACE_WIDTH'];
        yield ['ZBX_ICON_PREVIEW_HEIGHT'];
        yield ['ZBX_ICON_PREVIEW_WIDTH'];
        yield ['ZBX_INTERNAL_GROUP'];
        yield ['ZBX_ITEM_DELAY_DEFAULT'];
        yield ['ZBX_JAN_2038'];
        yield ['ZBX_LOGIN_ATTEMPTS'];
        yield ['ZBX_LOGIN_BLOCK'];
        yield ['ZBX_MAX_DATE'];
        yield ['ZBX_MAX_IMAGE_SIZE'];
        yield ['ZBX_MAX_PERIOD'];
        yield ['ZBX_MAX_PORT_NUMBER'];
        yield ['ZBX_MAX_TREND_DIFF'];
        yield ['ZBX_MIN_PERIOD'];
        yield ['ZBX_MIN_PORT_NUMBER'];
        yield ['ZBX_NOT_INTERNAL_GROUP'];
        yield ['ZBX_OVERVIEW_HELP_MIN_WIDTH'];
        yield ['ZBX_PERIOD_DEFAULT'];
        yield ['ZBX_PRECISION_10'];
        yield ['ZBX_PREG_DEF_FONT_STRING'];
        yield ['ZBX_PREG_DNS_FORMAT'];
        yield ['ZBX_PREG_EXPRESSION_LLD_MACROS'];
        yield ['ZBX_PREG_HOST_FORMAT'];
        yield ['ZBX_PREG_INTERNAL_NAMES'];
        yield ['ZBX_PREG_MACRO_NAME'];
        yield ['ZBX_PREG_MACRO_NAME_FORMAT'];
        yield ['ZBX_PREG_MACRO_NAME_LLD'];
        yield ['ZBX_PREG_NUMBER'];
        yield ['ZBX_PREG_PARAMS'];
        yield ['ZBX_PREG_PRINT'];
        yield ['ZBX_SCRIPT_EXECUTE_ON_AGENT'];
        yield ['ZBX_SCRIPT_EXECUTE_ON_SERVER'];
        yield ['ZBX_SCRIPT_TIMEOUT'];
        yield ['ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT'];
        yield ['ZBX_SCRIPT_TYPE_GLOBAL_SCRIPT'];
        yield ['ZBX_SCRIPT_TYPE_IPMI'];
        yield ['ZBX_SCRIPT_TYPE_SSH'];
        yield ['ZBX_SCRIPT_TYPE_TELNET'];
        yield ['ZBX_SESSION_ACTIVE'];
        yield ['ZBX_SESSION_PASSIVE'];
        yield ['ZBX_SOCKET_BYTES_LIMIT'];
        yield ['ZBX_SOCKET_TIMEOUT'];
        yield ['ZBX_SORT_DOWN'];
        yield ['ZBX_SORT_UP'];
        yield ['ZBX_TEXTAREA_2DIGITS_WIDTH'];
        yield ['ZBX_TEXTAREA_4DIGITS_WIDTH'];
        yield ['ZBX_TEXTAREA_BIG_WIDTH'];
        yield ['ZBX_TEXTAREA_COLOR_WIDTH'];
        yield ['ZBX_TEXTAREA_FILTER_BIG_WIDTH'];
        yield ['ZBX_TEXTAREA_FILTER_SMALL_WIDTH'];
        yield ['ZBX_TEXTAREA_FILTER_STANDARD_WIDTH'];
        yield ['ZBX_TEXTAREA_INTERFACE_DNS_WIDTH'];
        yield ['ZBX_TEXTAREA_INTERFACE_IP_WIDTH'];
        yield ['ZBX_TEXTAREA_INTERFACE_PORT_WIDTH'];
        yield ['ZBX_TEXTAREA_INTERFACE_USEIP_WIDTH'];
        yield ['ZBX_TEXTAREA_MACRO_VALUE_WIDTH'];
        yield ['ZBX_TEXTAREA_MACRO_WIDTH'];
        yield ['ZBX_TEXTAREA_NUMERIC_BIG_WIDTH'];
        yield ['ZBX_TEXTAREA_NUMERIC_STANDARD_WIDTH'];
        yield ['ZBX_TEXTAREA_SMALL_WIDTH'];
        yield ['ZBX_TEXTAREA_STANDARD_ROWS'];
        yield ['ZBX_TEXTAREA_STANDARD_WIDTH'];
        yield ['ZBX_TEXTAREA_TINY_WIDTH'];
        yield ['ZBX_TIME_SUFFIXES'];
        yield ['ZBX_UNITS_ROUNDOFF_LOWER_LIMIT'];
        yield ['ZBX_UNITS_ROUNDOFF_MIDDLE_LIMIT'];
        yield ['ZBX_UNITS_ROUNDOFF_THRESHOLD'];
        yield ['ZBX_UNITS_ROUNDOFF_UPPER_LIMIT'];
        yield ['ZBX_USER_ONLINE_TIME'];
        yield ['ZBX_VALID_ERROR'];
        yield ['ZBX_VALID_OK'];
        yield ['ZBX_VALID_WARNING'];
        yield ['ZBX_WIDGET_ROWS'];
    }
}
