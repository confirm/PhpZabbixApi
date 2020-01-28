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

final class <CLASSNAME_ZABBIX_API> implements <INTERFACENAME_ZABBIX_API_INTERFACE>
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
     * @var string
     */
    private $apiUrl = '';

    /**
     * Default params.
     *
     * @var array
     */
    private $defaultParams = array();

    /**
     * Auth string.
     *
     * @var string
     */
    private $authToken = '';

    /**
     * Request ID.
     *
     * @var int
     */
    private $id = 0;

    /**
     * Request array.
     *
     * @var array
     */
    private $request = array();

    /**
     * JSON encoded request string.
     *
     * @var string
     */
    private $requestEncoded = '';

    /**
     * JSON decoded response string.
     *
     * @var string
     */
    private $response = '';

    /**
     * Response object.
     *
     * @var \stdClass|array
     */
    private $responseDecoded;

    /**
     * Extra HTTP headers.
     *
     * @var string
     */
    private $extraHeaders = '';

    /**
     * SSL context.
     *
     * @var array
     */
    private $sslContext = array();

    /**
     * @param string $apiUrl API url (e.g. http://FQDN/zabbix/api_jsonrpc.php)
     * @param string $user Username for Zabbix API
     * @param string $password Password for Zabbix API
     * @param string $httpUser Username for HTTP basic authorization
     * @param string $httpPassword Password for HTTP basic authorization
     * @param string $authToken Already issued auth token (e.g. extracted from cookies)
     * @param array|null $sslContext SSL context for SSL-enabled connections
     */
    public function __construct($apiUrl = '', $user = '', $password = '', $httpUser = '', $httpPassword = '', $authToken = '', array $sslContext = null)
    {
        if ($apiUrl) {
            $this->setApiUrl($apiUrl);
        }

        if ($httpUser && $httpPassword) {
            $this->setBasicAuthorization($httpUser, $httpPassword);
        }

        if ($sslContext) {
            $this->setSslContext($sslContext);
        }

        if ($authToken) {
            $this->setAuthToken($authToken);
        } elseif ($user && $password) {
            $this->userLogin(array('user' => $user, 'password' => $password));
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
     * @return <CLASSNAME_ZABBIX_API>
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
     * @return <CLASSNAME_ZABBIX_API>
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
     * @return <CLASSNAME_ZABBIX_API>
     */
    public function setBasicAuthorization($user, $password)
    {
        if ($user && $password) {
            $this->extraHeaders = 'Authorization: Basic '.base64_encode($user.':'.$password);
        } else {
            $this->extraHeaders = '';
        }

        return $this;
    }

    /**
     * Sets the context for SSL-enabled connections.
     *
     * @see https://php.net/manual/en/context.ssl.php.
     *
     * @param array $context Array with the SSL context
     *
     * @return <CLASSNAME_ZABBIX_API>
     */
    public function setSslContext($context)
    {
        $this->sslContext = $context;

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
     * @return <CLASSNAME_ZABBIX_API>
     */
    public function setDefaultParams($defaultParams)
    {
        if (!is_array($defaultParams)) {
            throw new \InvalidArgumentException('Argument 1 passsed to '.__METHOD__.'() must be an array.');
        }

        $this->defaultParams = $defaultParams;

        return $this;
    }

    /**
     * Sets the flag to print communication requests/responses.
     *
     * @param bool $print Boolean if requests/responses should be printed out
     *
     * @return <CLASSNAME_ZABBIX_API>
     */
    public function printCommunication($print = true)
    {
        $this->printCommunication = (bool) $print;

        return $this;
    }

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
    public function request($method, $params = null, $resultArrayKey = '', $auth = true)
    {
        // sanity check and conversion for params array
        if (!$params) {
            $params = array();
        } elseif (!is_array($params)) {
            $params = array($params);
        }

        // generate ID
        $this->id = number_format(microtime(true), 4, '', '');

        // build request array
        $this->request = array(
            'jsonrpc' => '2.0',
            'method' => $method,
            'params' => $params,
            'id' => $this->id,
        );

        // add auth token if required
        if ($auth) {
            $this->request['auth'] = $this->authToken ?: null;
        }

        // encode request array
        $this->requestEncoded = json_encode($this->request);

        // debug logging
        if ($this->printCommunication) {
            echo 'API request: '.$this->requestEncoded;
        }

        // initialize context
        $context = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type: application/json-rpc'."\r\n".$this->extraHeaders,
                'content' => $this->requestEncoded,
            ),
        );
        if ($this->sslContext) {
            $context['ssl'] = $this->sslContext;
        }

        // create stream context
        $streamContext = stream_context_create($context);

        // get file handler
        $fileHandler = @fopen($this->getApiUrl(), 'r', false, $streamContext);
        if (!$fileHandler) {
            throw new Exception('Could not connect to "'.$this->getApiUrl().'"');
        }

        // get response
        $this->response = @stream_get_contents($fileHandler);

        // debug logging
        if ($this->printCommunication) {
            echo $this->response."\n";
        }

        // response verification
        if (false === $this->response) {
            throw new Exception('Could not read data from "'.$this->getApiUrl().'"');
        }

        // decode response
        $this->responseDecoded = json_decode($this->response);

        // validate response
        if (!is_object($this->responseDecoded) && !is_array($this->responseDecoded)) {
            throw new Exception('Could not decode JSON response.');
        }

        if (property_exists($this->responseDecoded, 'error') && $error = $this->responseDecoded->error) {
            $message = 'API error';
            if ($error = $this->responseDecoded->error) {
                $message .= ' '.$error->code.': '.$error->data;
            }

            throw new Exception($message.'.');
        }

        // return response
        if ($resultArrayKey && is_array($this->responseDecoded->result)) {
            return $this->convertToAssociatveArray($this->responseDecoded->result, $resultArrayKey);
        }

        return $this->responseDecoded->result;
    }

    /**
     * Returns the last JSON API request.
     *
     * @return string JSON request
     */
    public function getRequest()
    {
        return $this->requestEncoded;
    }

    /**
     * Returns the last JSON API response.
     *
     * @return string JSON response
     */
    public function getResponse()
    {
        return $this->response;
    }

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
    final public function userLogin($params = array(), $arrayKeyProperty = '', $tokenCacheDir = '/tmp')
    {
        // reset auth token
        $this->authToken = '';

        // build filename for cached auth token
        if ($tokenCacheDir && array_key_exists('user', $params) && is_dir($tokenCacheDir)) {
            $uid = function_exists('posix_getuid') ? posix_getuid() : -1;
            $tokenCacheFile = $tokenCacheDir.'/.zabbixapi-token-'.md5($params['user'].'|'.$uid);
        }

        // try to read cached auth token
        if (isset($tokenCacheFile) && is_file($tokenCacheFile)) {
            try {
                // get auth token and try to execute a user.get (dummy check)
                $this->authToken = file_get_contents($tokenCacheFile);
                $this->userGet(array('countOutput' => true));
            } catch (Exception $e) {
                // user.get failed, token invalid so reset it and remove file
                $this->authToken = '';
                unlink($tokenCacheFile);
            }
        }

        // no cached token found so far, so login (again)
        if (!$this->authToken) {
            // login to get the auth token
            $params = $this->getRequestParamsArray($params);
            $this->authToken = $this->request('user.login', $params, $arrayKeyProperty, false);

            // save cached auth token
            if (isset($tokenCacheFile)) {
                file_put_contents($tokenCacheFile, $this->authToken);
                chmod($tokenCacheFile, 0600);
            }
        }

        return $this->authToken;
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
     * @param string $arrayKeyProperty Object property for key of array
     *
     * @throws Exception
     *
     * @return mixed
     */
    final public function userLogout($params = array(), $arrayKeyProperty = '')
    {
        $params = $this->getRequestParamsArray($params);
        $response = $this->request('user.logout', $params, $arrayKeyProperty);
        $this->authToken = '';

        return $response;
    }
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
    public function <PHP_METHOD>($params = array(), $arrayKeyProperty = '')
    {
        return $this->request('<API_METHOD>', $this->getRequestParamsArray($params), $arrayKeyProperty, <IS_AUTHENTICATION_REQUIRED>);
    }
<!END_API_METHOD>
    /**
     * Converts an indexed array to an associative array.
     *
     * @param array $objectArray Indexed array with objects
     * @param string $useObjectProperty Object property to use as array key
     *
     * @return array associative array
     */
    private function convertToAssociatveArray(array $objectArray, $useObjectProperty)
    {
        // sanity check
        if (empty($objectArray) || !property_exists($objectArray[0], $useObjectProperty)) {
            return $objectArray;
        }

        // loop through array and replace keys
        $newObjectArray = array();
        foreach ($objectArray as $key => $object) {
            $newObjectArray[$object->{$useObjectProperty}] = $object;
        }

        // return associative array
        return $newObjectArray;
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
            // if params is a scalar value, turn it into an array
            $params = array($params);
        } elseif (!is_array($params)) {
            // if params isn't an array, create an empty one (e.g. for booleans, null)
            $params = array();
        }

        // if array isn't indexed, merge array with default params
        if (empty($params) || array_keys($params) !== range(0, count($params) - 1)) {
            $params = array_merge($this->getDefaultParams(), $params);
        }

        // return params
        return $params;
    }
}
