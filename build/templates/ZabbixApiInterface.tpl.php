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

namespace ZabbixApi;

interface <INTERFACENAME_ZABBIX_API_INTERFACE>
{<!START_API_CONSTANT>
    const <PHP_CONST_NAME> = <PHP_CONST_VALUE>;
<!END_API_CONSTANT>
    /**
     * Returns the API url for all requests.
     *
     * @return string API url
     */
    public function getApiUrl();

    /**
     * Sets the API url for all requests.
     *
     * @param string $apiUrl API url
     *
     * @return <CLASSNAME_ZABBIX_API>
     */
    public function setApiUrl($apiUrl);

    /**
     * Sets the API authorization ID.
     *
     * @param string $authToken API auth ID
     *
     * @return <CLASSNAME_ZABBIX_API>
     */
    public function setAuthToken($authToken);

    /**
     * Sets the username and password for the HTTP basic authorization.
     *
     * @param string $user HTTP basic authorization username
     * @param string $password HTTP basic authorization password
     *
     * @return <CLASSNAME_ZABBIX_API>
     */
    public function setBasicAuthorization($user, $password);

    /**
     * Sets the context for SSL-enabled connections.
     *
     * @see https://php.net/manual/en/context.ssl.php.
     *
     * @param array $context Array with the SSL context
     *
     * @return <CLASSNAME_ZABBIX_API>
     */
    public function setSslContext($context);

    /**
     * Returns the default params.
     *
     * @return array Array with default params
     */
    public function getDefaultParams();

    /**
     * Sets the default params.
     *
     * @param array $defaultParams Array with default params
     *
     * @throws Exception
     *
     * @return <CLASSNAME_ZABBIX_API>
     */
    public function setDefaultParams($defaultParams);

    /**
     * Sets the flag to print communication requests/responses.
     *
     * @param bool $print Boolean if requests/responses should be printed out
     *
     * @return <CLASSNAME_ZABBIX_API>
     */
    public function printCommunication($print = true);

    /**
     * Sends are request to the Zabbix API and returns the response as object.
     *
     * @param string $method Name of the API method
     * @param array|mixed|null $params Additional parameters
     * @param string $resultArrayKey
     * @param bool $auth Enable authentication (default true)
     *
     * @return mixed API JSON response
     */
    public function request($method, $params = null, $resultArrayKey = '', $auth = true);

    /**
     * Returns the last JSON API request.
     *
     * @return string JSON request
     */
    public function getRequest();

    /**
     * Returns the last JSON API response.
     *
     * @return string JSON response
     */
    public function getResponse();

    /**
     * Login into the API.
     *
     * This will also retrieves the auth Token, which will be used for any
     * further requests. Please be aware that by default the received auth
     * token will be cached on the filesystem.
     *
     * When a user is successfully logged in for the first time, the token will
     * be cached / stored in the $tokenCacheDir directory. For every future
     * request, the cached auth token will automatically be loaded and the
     * user.login is skipped. If the auth token is invalid/expired, user.login
     * will be executed, and the auth token will be cached again.
     *
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
     * @param string $arrayKeyProperty Object property for key of array
     * @param string $tokenCacheDir Path to a directory to store the auth token
     *
     * @throws Exception
     *
     * @return string
     */
    public function userLogin($params = array(), $arrayKeyProperty = '', $tokenCacheDir = '/tmp');

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
     * @param string $arrayKeyProperty Object property for key of array
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function userLogout($params = array(), $arrayKeyProperty = '');
<!START_API_METHOD>
    /**
     * Requests the Zabbix API and returns the response of the method "<API_METHOD>".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string $arrayKeyProperty Object property for key of array
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function <PHP_METHOD>($params = array(), $arrayKeyProperty = '');
<!END_API_METHOD>}
