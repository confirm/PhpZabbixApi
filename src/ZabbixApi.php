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

namespace Confirm\ZabbixApi;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\InvalidArgumentException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;

final class ZabbixApi implements ZabbixApiInterface, TokenCacheAwareInterface
{
    /**
     * Boolean if requests/responses should be printed out (JSON).
     *
     * @var bool
     */
    private $printCommunication = false;

    /**
     * API URL.
     *
     * @var string|null
     */
    private $apiUrl;

    /**
     * Default params.
     *
     * @var array
     */
    private $defaultParams = [];

    /**
     * @var string|null
     */
    private $user;

    /**
     * @var string|null
     */
    private $password;

    /**
     * Auth string.
     *
     * @var string|null
     */
    private $authToken;

    /**
     * Request ID.
     *
     * @var string
     */
    private $id;

    /**
     * @var ResponseInterface
     */
    private $response;

    /**
     * Response object.
     *
     * @var \stdClass|array
     */
    private $responseDecoded;

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var array<string, mixed>
     */
    private $requestOptions = [];

    /**
     * @var string|null
     */
    private $tokenCacheDir;

    /**
     * @param string|null $apiUrl API url (e.g. https://FQDN/zabbix/api_jsonrpc.php)
     * @param string|null $user Username for Zabbix API
     * @param string|null $password Password for Zabbix API
     * @param string|null $httpUser Username for HTTP basic authorization
     * @param string|null $httpPassword Password for HTTP basic authorization
     * @param string|null $authToken Already issued auth token (e.g. extracted from cookies)
     * @param array $clientOptions Client options
     */
    public function __construct($apiUrl = null, $user = null, $password = null, $httpUser = null, $httpPassword = null, $authToken = null, ClientInterface $client = null, array $clientOptions = [])
    {
        if (null !== $client && !empty($clientOptions)) {
            throw new \InvalidArgumentException('If argument 7 is provided, argument 8 must be omitted or passed with an empty array as value');
        }

        if (null !== $apiUrl) {
            $this->setApiUrl($apiUrl);
        }

        $clientOptions['base_uri'] = $this->apiUrl;

        if (!isset($clientOptions[RequestOptions::HEADERS])) {
            // Add the default "User-Agent" header.
            $clientOptions[RequestOptions::HEADERS] = ['User-Agent' => self::getDefaultAgentName()];
        } else {
            $isUserAgentSet = false;

            foreach (array_keys($clientOptions[RequestOptions::HEADERS]) as $name) {
                if ('user-agent' === strtolower($name)) {
                    $isUserAgentSet = true;

                    break;
                }
            }

            if (!$isUserAgentSet) {
                // Add the "User-Agent" header if one was not already set.
                $clientOptions[RequestOptions::HEADERS]['User-Agent'] = self::getDefaultAgentName();
            }
        }

        if (null !== $httpUser && null !== $httpPassword) {
            $this->setBasicAuthorization($httpUser, $httpPassword);
        }

        $this->client = null !== $client ? $client : new Client($clientOptions);

        if (null !== $authToken) {
            $this->setAuthToken($authToken);
        }

        if (null !== $user && null !== $password) {
            $this->user = $user;
            $this->password = $password;
        }
    }

    /**
     * Returns the API url for all requests.
     *
     * @return string API url
     */
    public function getApiUrl()
    {
        return $this->apiUrl;
    }

    /**
     * Sets the API url for all requests.
     *
     * @param string $apiUrl API url
     *
     * @return ZabbixApiInterface
     */
    public function setApiUrl($apiUrl)
    {
        $this->apiUrl = $apiUrl;

        return $this;
    }

    /**
     * Sets the API authorization ID.
     *
     * @param string $authToken API auth ID
     *
     * @return ZabbixApiInterface
     */
    public function setAuthToken($authToken)
    {
        $this->authToken = $authToken;

        return $this;
    }

    /**
     * Sets the username and password for the HTTP basic authorization.
     *
     * @param string $user HTTP basic authorization username
     * @param string $password HTTP basic authorization password
     *
     * @return ZabbixApiInterface
     */
    public function setBasicAuthorization($user, $password)
    {
        $this->requestOptions[RequestOptions::AUTH] = [$user, $password];

        return $this;
    }

    /**
     * Returns the default params.
     *
     * @return array Array with default params
     */
    public function getDefaultParams()
    {
        return $this->defaultParams;
    }

    /**
     * Sets the default params.
     *
     * @param array $defaultParams Array with default params
     *
     * @throws Exception
     *
     * @return ZabbixApiInterface
     */
    public function setDefaultParams(array $defaultParams)
    {
        $this->defaultParams = $defaultParams;

        return $this;
    }

    /**
     * Sets the flag to print communication requests/responses.
     *
     * @param bool $print Boolean if requests/responses should be printed out
     *
     * @return ZabbixApiInterface
     */
    public function printCommunication($print = true)
    {
        $this->printCommunication = (bool) $print;

        return $this;
    }

    public function setTokenCacheDir($directory)
    {
        $this->tokenCacheDir = $directory;
    }

    /**
     * Returns the last JSON API response.
     *
     * @return ResponseInterface|null
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Logout from the API.
     *
     * This will also reset the auth Token.
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param array $params Parameters to pass through
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function userLogout($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        $params = $this->getRequestParamsArray($params);
        $response = $this->request('user.logout', $params, $arrayKeyProperty, $assoc);
        $this->authToken = null;

        return $response;
    }

    /**
     * Requests the Zabbix API and returns the response of the method "action.create".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function actionCreate($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('action.create', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "action.delete".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function actionDelete($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('action.delete', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "action.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function actionGet($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('action.get', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "action.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function actionPk($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('action.pk', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "action.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function actionPkOption($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('action.pkoption', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "action.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function actionTableName($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('action.tablename', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "action.update".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function actionUpdate($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('action.update', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "action.validateoperationconditions".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function actionValidateOperationConditions($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('action.validateoperationconditions', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "action.validateoperationsintegrity".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function actionValidateOperationsIntegrity($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('action.validateoperationsintegrity', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "alert.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function alertGet($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('alert.get', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "alert.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function alertPk($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('alert.pk', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "alert.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function alertPkOption($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('alert.pkoption', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "alert.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function alertTableName($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('alert.tablename', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "api.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function apiPk($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('api.pk', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "api.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function apiPkOption($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('api.pkoption', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "api.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function apiTableName($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('api.tablename', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "apiinfo.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function apiinfoPk($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('apiinfo.pk', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "apiinfo.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function apiinfoPkOption($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('apiinfo.pkoption', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "apiinfo.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function apiinfoTableName($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('apiinfo.tablename', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "apiinfo.version".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function apiinfoVersion($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('apiinfo.version', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, false);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "application.create".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function applicationCreate($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('application.create', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "application.delete".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function applicationDelete($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('application.delete', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "application.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function applicationGet($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('application.get', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "application.massadd".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function applicationMassAdd($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('application.massadd', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "application.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function applicationPk($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('application.pk', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "application.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function applicationPkOption($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('application.pkoption', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "application.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function applicationTableName($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('application.tablename', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "application.update".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function applicationUpdate($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('application.update', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "configuration.export".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function configurationExport($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('configuration.export', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "configuration.import".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function configurationImport($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('configuration.import', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "configuration.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function configurationPk($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('configuration.pk', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "configuration.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function configurationPkOption($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('configuration.pkoption', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "configuration.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function configurationTableName($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('configuration.tablename', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "dcheck.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function dcheckGet($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('dcheck.get', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "dcheck.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function dcheckPk($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('dcheck.pk', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "dcheck.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function dcheckPkOption($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('dcheck.pkoption', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "dcheck.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function dcheckTableName($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('dcheck.tablename', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "dhost.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function dhostGet($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('dhost.get', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "dhost.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function dhostPk($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('dhost.pk', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "dhost.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function dhostPkOption($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('dhost.pkoption', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "dhost.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function dhostTableName($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('dhost.tablename', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "discoveryrule.copy".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function discoveryruleCopy($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('discoveryrule.copy', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "discoveryrule.create".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function discoveryruleCreate($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('discoveryrule.create', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "discoveryrule.delete".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function discoveryruleDelete($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('discoveryrule.delete', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "discoveryrule.findinterfaceforitem".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function discoveryruleFindInterfaceForItem($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('discoveryrule.findinterfaceforitem', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "discoveryrule.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function discoveryruleGet($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('discoveryrule.get', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "discoveryrule.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function discoveryrulePk($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('discoveryrule.pk', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "discoveryrule.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function discoveryrulePkOption($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('discoveryrule.pkoption', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "discoveryrule.synctemplates".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function discoveryruleSyncTemplates($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('discoveryrule.synctemplates', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "discoveryrule.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function discoveryruleTableName($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('discoveryrule.tablename', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "discoveryrule.update".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function discoveryruleUpdate($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('discoveryrule.update', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "drule.create".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function druleCreate($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('drule.create', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "drule.delete".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function druleDelete($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('drule.delete', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "drule.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function druleGet($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('drule.get', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "drule.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function drulePk($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('drule.pk', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "drule.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function drulePkOption($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('drule.pkoption', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "drule.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function druleTableName($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('drule.tablename', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "drule.update".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function druleUpdate($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('drule.update', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "dservice.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function dserviceGet($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('dservice.get', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "dservice.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function dservicePk($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('dservice.pk', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "dservice.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function dservicePkOption($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('dservice.pkoption', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "dservice.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function dserviceTableName($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('dservice.tablename', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "event.acknowledge".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function eventAcknowledge($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('event.acknowledge', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "event.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function eventGet($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('event.get', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "event.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function eventPk($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('event.pk', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "event.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function eventPkOption($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('event.pkoption', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "event.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function eventTableName($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('event.tablename', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "graph.create".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function graphCreate($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('graph.create', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "graph.delete".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function graphDelete($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('graph.delete', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "graph.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function graphGet($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('graph.get', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "graph.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function graphPk($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('graph.pk', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "graph.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function graphPkOption($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('graph.pkoption', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "graph.synctemplates".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function graphSyncTemplates($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('graph.synctemplates', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "graph.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function graphTableName($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('graph.tablename', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "graph.update".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function graphUpdate($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('graph.update', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "graphitem.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function graphitemGet($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('graphitem.get', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "graphitem.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function graphitemPk($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('graphitem.pk', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "graphitem.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function graphitemPkOption($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('graphitem.pkoption', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "graphitem.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function graphitemTableName($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('graphitem.tablename', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "graphprototype.create".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function graphprototypeCreate($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('graphprototype.create', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "graphprototype.delete".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function graphprototypeDelete($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('graphprototype.delete', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "graphprototype.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function graphprototypeGet($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('graphprototype.get', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "graphprototype.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function graphprototypePk($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('graphprototype.pk', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "graphprototype.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function graphprototypePkOption($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('graphprototype.pkoption', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "graphprototype.synctemplates".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function graphprototypeSyncTemplates($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('graphprototype.synctemplates', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "graphprototype.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function graphprototypeTableName($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('graphprototype.tablename', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "graphprototype.update".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function graphprototypeUpdate($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('graphprototype.update', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "history.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function historyGet($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('history.get', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "history.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function historyPk($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('history.pk', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "history.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function historyPkOption($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('history.pkoption', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "history.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function historyTableName($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('history.tablename', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "host.create".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostCreate($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('host.create', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "host.delete".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostDelete($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('host.delete', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "host.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostGet($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('host.get', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "host.massadd".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostMassAdd($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('host.massadd', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "host.massremove".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostMassRemove($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('host.massremove', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "host.massupdate".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostMassUpdate($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('host.massupdate', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "host.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostPk($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('host.pk', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "host.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostPkOption($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('host.pkoption', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "host.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostTableName($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('host.tablename', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "host.update".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostUpdate($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('host.update', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "hostgroup.create".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostgroupCreate($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('hostgroup.create', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "hostgroup.delete".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostgroupDelete($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('hostgroup.delete', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "hostgroup.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostgroupGet($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('hostgroup.get', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "hostgroup.massadd".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostgroupMassAdd($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('hostgroup.massadd', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "hostgroup.massremove".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostgroupMassRemove($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('hostgroup.massremove', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "hostgroup.massupdate".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostgroupMassUpdate($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('hostgroup.massupdate', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "hostgroup.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostgroupPk($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('hostgroup.pk', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "hostgroup.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostgroupPkOption($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('hostgroup.pkoption', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "hostgroup.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostgroupTableName($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('hostgroup.tablename', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "hostgroup.update".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostgroupUpdate($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('hostgroup.update', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "hostinterface.create".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostinterfaceCreate($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('hostinterface.create', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "hostinterface.delete".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostinterfaceDelete($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('hostinterface.delete', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "hostinterface.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostinterfaceGet($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('hostinterface.get', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "hostinterface.massadd".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostinterfaceMassAdd($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('hostinterface.massadd', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "hostinterface.massremove".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostinterfaceMassRemove($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('hostinterface.massremove', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "hostinterface.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostinterfacePk($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('hostinterface.pk', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "hostinterface.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostinterfacePkOption($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('hostinterface.pkoption', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "hostinterface.replacehostinterfaces".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostinterfaceReplaceHostInterfaces($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('hostinterface.replacehostinterfaces', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "hostinterface.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostinterfaceTableName($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('hostinterface.tablename', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "hostinterface.update".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostinterfaceUpdate($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('hostinterface.update', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "hostprototype.create".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostprototypeCreate($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('hostprototype.create', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "hostprototype.delete".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostprototypeDelete($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('hostprototype.delete', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "hostprototype.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostprototypeGet($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('hostprototype.get', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "hostprototype.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostprototypePk($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('hostprototype.pk', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "hostprototype.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostprototypePkOption($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('hostprototype.pkoption', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "hostprototype.synctemplates".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostprototypeSyncTemplates($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('hostprototype.synctemplates', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "hostprototype.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostprototypeTableName($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('hostprototype.tablename', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "hostprototype.update".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostprototypeUpdate($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('hostprototype.update', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "httptest.create".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function httptestCreate($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('httptest.create', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "httptest.delete".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function httptestDelete($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('httptest.delete', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "httptest.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function httptestGet($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('httptest.get', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "httptest.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function httptestPk($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('httptest.pk', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "httptest.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function httptestPkOption($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('httptest.pkoption', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "httptest.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function httptestTableName($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('httptest.tablename', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "httptest.update".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function httptestUpdate($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('httptest.update', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "iconmap.create".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function iconmapCreate($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('iconmap.create', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "iconmap.delete".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function iconmapDelete($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('iconmap.delete', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "iconmap.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function iconmapGet($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('iconmap.get', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "iconmap.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function iconmapPk($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('iconmap.pk', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "iconmap.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function iconmapPkOption($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('iconmap.pkoption', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "iconmap.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function iconmapTableName($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('iconmap.tablename', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "iconmap.update".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function iconmapUpdate($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('iconmap.update', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "image.create".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function imageCreate($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('image.create', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "image.delete".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function imageDelete($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('image.delete', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "image.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function imageGet($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('image.get', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "image.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function imagePk($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('image.pk', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "image.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function imagePkOption($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('image.pkoption', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "image.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function imageTableName($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('image.tablename', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "image.update".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function imageUpdate($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('image.update', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "item.addrelatedobjects".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function itemAddRelatedObjects($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('item.addrelatedobjects', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "item.create".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function itemCreate($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('item.create', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "item.delete".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function itemDelete($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('item.delete', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "item.findinterfaceforitem".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function itemFindInterfaceForItem($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('item.findinterfaceforitem', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "item.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function itemGet($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('item.get', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "item.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function itemPk($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('item.pk', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "item.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function itemPkOption($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('item.pkoption', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "item.synctemplates".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function itemSyncTemplates($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('item.synctemplates', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "item.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function itemTableName($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('item.tablename', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "item.update".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function itemUpdate($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('item.update', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "item.validateinventorylinks".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function itemValidateInventoryLinks($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('item.validateinventorylinks', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "itemprototype.addrelatedobjects".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function itemprototypeAddRelatedObjects($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('itemprototype.addrelatedobjects', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "itemprototype.create".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function itemprototypeCreate($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('itemprototype.create', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "itemprototype.delete".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function itemprototypeDelete($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('itemprototype.delete', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "itemprototype.findinterfaceforitem".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function itemprototypeFindInterfaceForItem($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('itemprototype.findinterfaceforitem', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "itemprototype.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function itemprototypeGet($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('itemprototype.get', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "itemprototype.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function itemprototypePk($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('itemprototype.pk', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "itemprototype.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function itemprototypePkOption($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('itemprototype.pkoption', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "itemprototype.synctemplates".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function itemprototypeSyncTemplates($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('itemprototype.synctemplates', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "itemprototype.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function itemprototypeTableName($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('itemprototype.tablename', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "itemprototype.update".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function itemprototypeUpdate($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('itemprototype.update', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "maintenance.create".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function maintenanceCreate($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('maintenance.create', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "maintenance.delete".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function maintenanceDelete($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('maintenance.delete', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "maintenance.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function maintenanceGet($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('maintenance.get', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "maintenance.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function maintenancePk($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('maintenance.pk', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "maintenance.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function maintenancePkOption($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('maintenance.pkoption', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "maintenance.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function maintenanceTableName($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('maintenance.tablename', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "maintenance.update".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function maintenanceUpdate($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('maintenance.update', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "map.create".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function mapCreate($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('map.create', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "map.delete".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function mapDelete($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('map.delete', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "map.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function mapGet($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('map.get', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "map.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function mapPk($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('map.pk', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "map.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function mapPkOption($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('map.pkoption', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "map.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function mapTableName($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('map.tablename', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "map.update".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function mapUpdate($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('map.update', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "mediatype.create".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function mediatypeCreate($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('mediatype.create', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "mediatype.delete".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function mediatypeDelete($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('mediatype.delete', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "mediatype.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function mediatypeGet($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('mediatype.get', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "mediatype.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function mediatypePk($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('mediatype.pk', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "mediatype.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function mediatypePkOption($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('mediatype.pkoption', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "mediatype.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function mediatypeTableName($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('mediatype.tablename', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "mediatype.update".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function mediatypeUpdate($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('mediatype.update', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "proxy.create".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function proxyCreate($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('proxy.create', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "proxy.delete".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function proxyDelete($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('proxy.delete', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "proxy.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function proxyGet($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('proxy.get', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "proxy.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function proxyPk($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('proxy.pk', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "proxy.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function proxyPkOption($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('proxy.pkoption', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "proxy.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function proxyTableName($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('proxy.tablename', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "proxy.update".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function proxyUpdate($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('proxy.update', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "screen.create".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function screenCreate($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('screen.create', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "screen.delete".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function screenDelete($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('screen.delete', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "screen.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function screenGet($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('screen.get', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "screen.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function screenPk($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('screen.pk', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "screen.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function screenPkOption($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('screen.pkoption', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "screen.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function screenTableName($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('screen.tablename', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "screen.update".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function screenUpdate($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('screen.update', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "screenitem.create".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function screenitemCreate($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('screenitem.create', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "screenitem.delete".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function screenitemDelete($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('screenitem.delete', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "screenitem.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function screenitemGet($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('screenitem.get', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "screenitem.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function screenitemPk($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('screenitem.pk', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "screenitem.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function screenitemPkOption($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('screenitem.pkoption', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "screenitem.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function screenitemTableName($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('screenitem.tablename', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "screenitem.update".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function screenitemUpdate($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('screenitem.update', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "screenitem.updatebyposition".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function screenitemUpdateByPosition($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('screenitem.updatebyposition', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "script.create".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function scriptCreate($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('script.create', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "script.delete".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function scriptDelete($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('script.delete', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "script.execute".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function scriptExecute($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('script.execute', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "script.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function scriptGet($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('script.get', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "script.getscriptsbyhosts".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function scriptGetScriptsByHosts($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('script.getscriptsbyhosts', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "script.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function scriptPk($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('script.pk', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "script.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function scriptPkOption($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('script.pkoption', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "script.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function scriptTableName($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('script.tablename', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "script.update".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function scriptUpdate($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('script.update', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "service.adddependencies".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function serviceAddDependencies($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('service.adddependencies', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "service.addtimes".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function serviceAddTimes($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('service.addtimes', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "service.create".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function serviceCreate($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('service.create', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "service.delete".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function serviceDelete($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('service.delete', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "service.deletedependencies".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function serviceDeleteDependencies($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('service.deletedependencies', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "service.deletetimes".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function serviceDeleteTimes($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('service.deletetimes', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "service.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function serviceGet($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('service.get', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "service.getsla".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function serviceGetSla($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('service.getsla', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "service.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function servicePk($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('service.pk', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "service.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function servicePkOption($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('service.pkoption', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "service.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function serviceTableName($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('service.tablename', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "service.update".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function serviceUpdate($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('service.update', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "service.validateaddtimes".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function serviceValidateAddTimes($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('service.validateaddtimes', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "service.validatedelete".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function serviceValidateDelete($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('service.validatedelete', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "service.validateupdate".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function serviceValidateUpdate($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('service.validateupdate', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "template.create".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function templateCreate($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('template.create', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "template.delete".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function templateDelete($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('template.delete', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "template.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function templateGet($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('template.get', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "template.massadd".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function templateMassAdd($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('template.massadd', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "template.massremove".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function templateMassRemove($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('template.massremove', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "template.massupdate".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function templateMassUpdate($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('template.massupdate', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "template.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function templatePk($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('template.pk', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "template.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function templatePkOption($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('template.pkoption', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "template.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function templateTableName($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('template.tablename', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "template.update".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function templateUpdate($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('template.update', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "templatescreen.copy".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function templatescreenCopy($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('templatescreen.copy', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "templatescreen.create".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function templatescreenCreate($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('templatescreen.create', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "templatescreen.delete".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function templatescreenDelete($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('templatescreen.delete', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "templatescreen.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function templatescreenGet($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('templatescreen.get', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "templatescreen.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function templatescreenPk($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('templatescreen.pk', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "templatescreen.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function templatescreenPkOption($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('templatescreen.pkoption', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "templatescreen.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function templatescreenTableName($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('templatescreen.tablename', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "templatescreen.update".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function templatescreenUpdate($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('templatescreen.update', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "templatescreenitem.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function templatescreenitemGet($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('templatescreenitem.get', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "templatescreenitem.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function templatescreenitemPk($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('templatescreenitem.pk', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "templatescreenitem.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function templatescreenitemPkOption($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('templatescreenitem.pkoption', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "templatescreenitem.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function templatescreenitemTableName($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('templatescreenitem.tablename', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "trend.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function trendGet($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('trend.get', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "trend.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function trendPk($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('trend.pk', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "trend.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function trendPkOption($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('trend.pkoption', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "trend.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function trendTableName($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('trend.tablename', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "trigger.adddependencies".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function triggerAddDependencies($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('trigger.adddependencies', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "trigger.create".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function triggerCreate($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('trigger.create', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "trigger.delete".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function triggerDelete($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('trigger.delete', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "trigger.deletedependencies".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function triggerDeleteDependencies($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('trigger.deletedependencies', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "trigger.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function triggerGet($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('trigger.get', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "trigger.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function triggerPk($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('trigger.pk', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "trigger.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function triggerPkOption($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('trigger.pkoption', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "trigger.synctemplatedependencies".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function triggerSyncTemplateDependencies($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('trigger.synctemplatedependencies', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "trigger.synctemplates".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function triggerSyncTemplates($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('trigger.synctemplates', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "trigger.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function triggerTableName($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('trigger.tablename', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "trigger.update".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function triggerUpdate($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('trigger.update', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "triggerprototype.create".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function triggerprototypeCreate($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('triggerprototype.create', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "triggerprototype.delete".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function triggerprototypeDelete($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('triggerprototype.delete', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "triggerprototype.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function triggerprototypeGet($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('triggerprototype.get', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "triggerprototype.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function triggerprototypePk($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('triggerprototype.pk', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "triggerprototype.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function triggerprototypePkOption($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('triggerprototype.pkoption', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "triggerprototype.synctemplatedependencies".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function triggerprototypeSyncTemplateDependencies($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('triggerprototype.synctemplatedependencies', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "triggerprototype.synctemplates".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function triggerprototypeSyncTemplates($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('triggerprototype.synctemplates', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "triggerprototype.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function triggerprototypeTableName($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('triggerprototype.tablename', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "triggerprototype.update".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function triggerprototypeUpdate($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('triggerprototype.update', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "user.addmedia".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function userAddMedia($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('user.addmedia', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "user.checkauthentication".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function userCheckAuthentication($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('user.checkauthentication', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, false);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "user.create".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function userCreate($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('user.create', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "user.delete".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function userDelete($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('user.delete', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "user.deletemedia".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function userDeleteMedia($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('user.deletemedia', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "user.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function userGet($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('user.get', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "user.login".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function userLogin($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('user.login', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, false);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "user.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function userPk($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('user.pk', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "user.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function userPkOption($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('user.pkoption', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "user.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function userTableName($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('user.tablename', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "user.update".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function userUpdate($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('user.update', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "user.updatemedia".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function userUpdateMedia($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('user.updatemedia', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "user.updateprofile".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function userUpdateProfile($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('user.updateprofile', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "usergroup.create".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function usergroupCreate($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('usergroup.create', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "usergroup.delete".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function usergroupDelete($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('usergroup.delete', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "usergroup.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function usergroupGet($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('usergroup.get', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "usergroup.massadd".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function usergroupMassAdd($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('usergroup.massadd', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "usergroup.massupdate".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function usergroupMassUpdate($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('usergroup.massupdate', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "usergroup.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function usergroupPk($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('usergroup.pk', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "usergroup.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function usergroupPkOption($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('usergroup.pkoption', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "usergroup.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function usergroupTableName($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('usergroup.tablename', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "usergroup.update".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function usergroupUpdate($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('usergroup.update', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "usermacro.create".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function usermacroCreate($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('usermacro.create', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "usermacro.createglobal".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function usermacroCreateGlobal($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('usermacro.createglobal', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "usermacro.delete".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function usermacroDelete($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('usermacro.delete', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "usermacro.deleteglobal".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function usermacroDeleteGlobal($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('usermacro.deleteglobal', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "usermacro.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function usermacroGet($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('usermacro.get', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "usermacro.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function usermacroPk($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('usermacro.pk', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "usermacro.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function usermacroPkOption($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('usermacro.pkoption', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "usermacro.replacemacros".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function usermacroReplaceMacros($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('usermacro.replacemacros', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "usermacro.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function usermacroTableName($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('usermacro.tablename', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "usermacro.update".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function usermacroUpdate($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('usermacro.update', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "usermacro.updateglobal".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function usermacroUpdateGlobal($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('usermacro.updateglobal', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "usermedia.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function usermediaGet($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('usermedia.get', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "usermedia.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function usermediaPk($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('usermedia.pk', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "usermedia.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function usermediaPkOption($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('usermedia.pkoption', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "usermedia.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function usermediaTableName($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('usermedia.tablename', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "valuemap.create".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function valuemapCreate($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('valuemap.create', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "valuemap.delete".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function valuemapDelete($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('valuemap.delete', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "valuemap.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function valuemapGet($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('valuemap.get', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "valuemap.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function valuemapPk($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('valuemap.pk', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "valuemap.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function valuemapPkOption($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('valuemap.pkoption', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "valuemap.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function valuemapTableName($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('valuemap.tablename', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Requests the Zabbix API and returns the response of the method "valuemap.update".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function valuemapUpdate($params = [], $arrayKeyProperty = null, $assoc = true)
    {
        return $this->request('valuemap.update', $this->getRequestParamsArray($params), $arrayKeyProperty, $assoc, true);
    }

    /**
     * Sends are request to the Zabbix API and returns the response as object.
     *
     * @param string $method Name of the API method
     * @param array|mixed|null $params Additional parameters
     * @param string|null $resultArrayKey
     * @param bool $assoc Return the result as an associative array
     * @param bool $auth Enable authentication (default true)
     * @param int $remainingAuthAttempts Number of remaining authentication attempts before failing
     *
     * @return mixed API JSON response
     */
    private function request($method, $params = null, $resultArrayKey = null, $assoc = true, $auth = true, $remainingAuthAttempts = 1)
    {
        // Sanity check and conversion for params array.
        if (!$params) {
            $params = [];
        } elseif (!is_array($params)) {
            $params = [$params];
        }

        // Generate request ID.
        $this->id = number_format(microtime(true), 4, '', '');

        // Build request payload.
        $requestPayload = [
            'jsonrpc' => '2.0',
            'method' => $method,
            'params' => $params,
            'id' => $this->id,
        ];

        // Add auth token if required.
        if ($auth && null !== $this->user) {
            $requestPayload['auth'] = $this->getAuthToken();
        }

        if ($this->printCommunication) {
            $this->requestOptions[RequestOptions::DEBUG] = true;
        }

        try {
            $this->response = $this->client->request('POST', $this->apiUrl, $this->requestOptions + [
                RequestOptions::HEADERS => ['Content-type' => 'application/json-rpc'],
                RequestOptions::JSON => $requestPayload,
            ]);
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $this->response = $e->getResponse();

                throw new Exception(sprintf('%s: %s', $e->getMessage(), $this->response->getBody()->getContents()), $e->getCode(), $e);
            }

            throw new Exception($e->getMessage(), $e->getCode(), $e);
        } catch (TransferException $e) {
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        } finally {
            // Debug logging.
            if ($this->printCommunication && null !== $this->response) {
                echo $this->response->getBody()."\n";
            }
        }

        try {
            $response = $this->decodeResponse($this->response, $resultArrayKey, $assoc);
        } catch (Exception $e) {
            // If the request is not authorized due an authentication issue, attempt to login again and retry the operation.
            if ($auth && self::UNAUTHORIZED_ERROR_CODE === $e->getCode() &&
                in_array($e->getMessage(), [self::UNAUTHORIZED_ERROR_MESSAGE, self::UNAUTHORIZED_SESSION_TERMINATED_ERROR_MESSAGE], true) &&
                $remainingAuthAttempts > 0 && null !== $this->user && null !== $this->password
            ) {
                $this->getAuthToken(false);

                return $this->request($method, $params, $resultArrayKey, $auth, $assoc, --$remainingAuthAttempts);
            }

            throw $e;
        }

        return $response;
    }

    /**
     * Returns a params array for the request.
     *
     * This method will automatically convert all provided types into a correct
     * array. Which means:
     *
     *      - arrays will not be converted (indexed & associative)
     *      - scalar values will be converted into an one-element array (indexed)
     *      - other values will result in an empty array
     *
     * Afterwards the array will be merged with all default params, while the
     * default params have a lower priority (passed array will overwrite default
     * params). But there is an Exception for merging: If the passed array is an
     * indexed array, the default params will not be merged. This is because
     * there are some API methods, which are expecting a simple JSON array (aka
     * PHP indexed array) instead of an object (aka PHP associative array).
     * Example for this behavior are delete operations, which are directly
     * expecting an array of IDs '[ 1,2,3 ]' instead of '{ ids: [ 1,2,3 ] }'.
     *
     * @param mixed $params Params array
     *
     * @return array
     */
    private function getRequestParamsArray($params)
    {
        if (is_scalar($params)) {
            // If params is a scalar value, turn it into an array.
            $params = [$params];
        } elseif (!is_array($params)) {
            // If params isn't an array, create an empty one (e.g. for booleans, null).
            $params = [];
        }

        $paramsCount = count($params);

        // If array isn't indexed, merge array with default params.
        if (0 === $paramsCount || array_keys($params) !== range(0, $paramsCount - 1)) {
            $params = array_merge($this->getDefaultParams(), $params);
        }

        return $params;
    }

    /**
     * @param string|null $resultArrayKey
     * @param bool $assoc
     *
     * @throws Exception
     *
     * @return mixed The decoded JSON data
     */
    private function decodeResponse(ResponseInterface $response, $resultArrayKey = null, $assoc = true)
    {
        $content = $response->getBody();

        try {
            $this->responseDecoded = \GuzzleHttp\json_decode($content, $assoc);
        } catch (InvalidArgumentException $ex) {
            throw new Exception(sprintf('Response body could not be parsed since the JSON structure could not be decoded: %s', $content), $ex->getCode(), $ex);
        }

        if ($assoc) {
            if (isset($this->responseDecoded['error'])) {
                throw new Exception($this->responseDecoded['error']['data'], $this->responseDecoded['error']['code']);
            }
            if (null !== $resultArrayKey) {
                return self::convertToAssociatveArray($this->responseDecoded['result'], $resultArrayKey);
            }

            return $this->responseDecoded['result'];
        }

        if (property_exists($this->responseDecoded, 'error') && $error = $this->responseDecoded->error) {
            throw new Exception($error->data, $error->code);
        }

        if (null !== $resultArrayKey) {
            return self::convertToAssociatveArray($this->responseDecoded->result, $resultArrayKey);
        }

        return $this->responseDecoded->result;
    }

    private function getAuthToken($fromCache = true)
    {
        if ($fromCache && null !== $this->authToken) {
            return $this->authToken;
        }

        $tokenCacheDir = null !== $this->tokenCacheDir ? $this->tokenCacheDir : sys_get_temp_dir();
        $tokenCacheFile = null;

        // Build filename for cached auth token.
        if ($tokenCacheDir && is_dir($tokenCacheDir)) {
            $uid = function_exists('posix_getuid') ? posix_getuid() : -1;
            $tokenCacheFile = $tokenCacheDir.'/.zabbixapi-token-'.md5($this->user.'|'.$uid);
        }

        if ($fromCache) {
            // Try to read cached auth token.
            if (null !== $tokenCacheFile && is_file($tokenCacheFile)) {
                $cachedToken = @file_get_contents($tokenCacheFile);

                if (false === $cachedToken) {
                    // Unlink corrupted cached token file.
                    @unlink($tokenCacheFile);

                    throw new Exception('Failed to read cached token.');
                }

                $this->authToken = $cachedToken;
            }
        }

        // No cached token found so far, so login.
        if (!$fromCache || null === $this->authToken) {
            // login to get the auth token
            $params = $this->getRequestParamsArray(['user' => $this->user, 'password' => $this->password]);
            $this->authToken = $this->userLogin($params);

            // Persist cached auth token.
            if (null !== $tokenCacheFile) {
                @file_put_contents($tokenCacheFile, $this->authToken);
                @chmod($tokenCacheFile, 0600);
            }
        }

        return $this->authToken;
    }

    /**
     * Returns the array or the instance of `\stdClass` indexed by the given parameter or property
     * name.
     *
     * @param array|\stdClass|mixed $objectOrArray Indexed array with objects
     * @param string $useObjectProperty Object property to use as array key
     *
     * @return array<string, mixed>|\stdClass
     */
    private static function convertToAssociatveArray($objectOrArray, $useObjectProperty)
    {
        if (is_array($objectOrArray)) {
            // Sanity check.
            if (!empty($objectOrArray) && !isset(current($objectOrArray)[$useObjectProperty])) {
                throw new \InvalidArgumentException(sprintf('Parameter "%s" does not exist in the given elements.', $useObjectProperty));
            }

            // Return associative array.
            return array_column($objectOrArray, null, $useObjectProperty);
        }

        if (is_object($objectOrArray)) {
            $objectVars = get_object_vars($objectOrArray);

            // Sanity check.
            if (!empty($objectVars) && !property_exists(current($objectVars), $useObjectProperty)) {
                throw new \InvalidArgumentException(sprintf('Property "%s" does not exist in the given elements.', $useObjectProperty));
            }

            // Loop through array and replace keys.
            $newObject = new \stdClass();
            foreach ($objectVars as $key => $object) {
                $newObject->{$object->{$useObjectProperty}} = $object;
            }

            // Return object indexed by the given property value.
            return $newObject;
        }

        throw new \InvalidArgumentException(sprintf('Argument 1 passed to "%s()" must be of type "array" or an instance of "\stdClass", "%s" given.', __METHOD__, gettype($objectOrArray)));
    }

    /**
     * @return string
     */
    private static function getDefaultAgentName()
    {
        return sprintf('PhpZabbixApi/%s %s', self::PHP_ZABBIX_API_VERSION, \GuzzleHttp\default_user_agent());
    }
}
