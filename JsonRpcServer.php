<?php
/**
 * PHP implementation of JSON-RPC 2.0
 * Server
 * Specification: http://www.jsonrpc.org/specification
 *
 * @package JsonRpcPhp
 * @author Mark Rolich <mark.rolich@gmail.com>
 */
class JsonRpcServer
{
    /**
     * @var mixed - array of services (objects) to plug
     */
    private $services;

    /**
     * @var mixed - list of service method as keys and parameters list as values
     */
    private $queue;

    /**
     * Plugs service to server
     *
     * @param $serviceObj mixed - service object
     */
    public function addService($serviceObj)
    {
        $this->services[] = $serviceObj;
    }

    /**
     * Adds method with linked parameters list to queue
     *
     * @param $service mixed - service object
     * @param $method string - service method name
     */
    private function enqueue($service, $method)
    {
        if (!isset($this->queue[$method])) {
            $rMethod = new ReflectionMethod($service, $method);
            $rParams = $rMethod->getParameters();

            $this->queue[$method] = array(
                'required' => array(),
                'optional' => array()
            );

            foreach ($rParams as $param) {
                if ($param->isOptional() === false) {
                    array_push($this->queue[$method]['required'], $param->getName());
                } else {
                    $this->queue[$method]['optional'][$param->getName()] = $param->getDefaultValue();
                }
            }
        }
    }

    /**
     * Validates JSON string
     *
     * @param $request mixed - request object
     * @throws JsonRpcInvalidJsonException
     */
    private function validateJson($request)
    {
        if ($request === null) {
            throw new JsonRpcInvalidJsonException();
        }
    }

    /**
     * Validates batch JSON string (array of JSON strings)
     *
     * @param $request mixed - requests array
     * @throws JsonRpcInvalidJsonException
     */
    private function validateBatchJson($request)
    {
        $check = array_filter($request, 'is_string');

        if (!empty($check)) {
            throw new JsonRpcInvalidJsonException();
        }
    }

    /**
     * Checks if batch request array is not empty
     *
     * @param $request mixed - request array
     * @throws JsonRpcInvalidRequestException
     */
    private function validateBatchRequest($request)
    {
        if (empty($request)) {
            throw new JsonRpcInvalidRequestException();
        }
    }

    /**
     * Validates if method exists in one of the plugged service objects
     * and returns service object on success
     *
     * @param $method string - service method name
     * @throws JsonRpcInvalidMethodException
     * @return mixed - service object to which method belongs
     */
    private function validateMethod($method)
    {
        $result = 0;
        $service = null;

        foreach ($this->services as $service) {
            if (method_exists($service, $method)) {
                $result = 1;
                break;
            }
        }

        if ($result === 0) {
            throw new JsonRpcInvalidMethodException();
        }

        return $service;
    }

    /**
     * Validates request structure against
     * JSON-RPC 2.0 Request object specification
     * (http://www.jsonrpc.org/specification#request_object)
     *
     * @param $request mixed - request object
     * @throws JsonRpcInvalidRequestException
     */
    private function validateRequest($request)
    {
        $pattern = '/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/';

        $notRequest = (!isset($request->jsonrpc) || !isset($request->method));
        $notMethod = (isset($request->method) && preg_match($pattern, $request->method) === 0);
        $notParams = false;

        if (isset($request->params)) {
            $params = $request->params;
            $notStructured = (!is_array($params) && !is_object($params));
            $notParams = (count((array)$params) === 0 || $notStructured);
        }

        if ($notRequest || $notMethod || $notParams) {
            throw new JsonRpcInvalidRequestException();
        }
    }

    /**
     * Checks if request method expects parameters
     * and parameters are provided with the request
     *
     * @param $request mixed - request object
     * @throws JsonRpcInvalidParamsException
     */
    private function areParamsExpected($request)
    {
        $rParams = $this->queue[$request->method]['required'];

        if (count($rParams) > 0 && !isset($request->params)) {
            throw new JsonRpcInvalidParamsException();
        }
    }

    /**
     * Checks if request positional parameters array length
     * equal to method parameters list length
     *
     * @param $method string - method name
     * @param $params mixed - parameters array
     * @throws JsonRpcInvalidParamsException
     */
    private function validatePosParams($method, $params)
    {
        if (count($params) < count($this->queue[$method]['required'])) {
            throw new JsonRpcInvalidParamsException();
        }
    }

    /**
     * Checks if request named parameters
     * equal to method parameters list
     *
     * @param $method string - method name
     * @param $params mixed - parameters object
     * @throws JsonRpcInvalidParamsException
     */
    private function validateNamedParams($method, $params)
    {
        $params = (array)$params;
        $rParams = array_flip($this->queue[$method]['required']);
        $oParams = $this->queue[$method]['optional'];

        $diff = array_diff_key($params, $rParams);
        $oDiff = array_diff_key($diff, $oParams);

        if (!empty($diff) && !empty($oDiff)) {
            throw new JsonRpcInvalidParamsException();
        }
    }

    /**
     * Sorts request named parameters
     * according to method parameters
     *
     * @param $method string - method name
     * @param $params mixed - parameters object
     * @return array - sorted parameters array
     */
    private function sortNamedParams($method, $params)
    {
        $params = (array)$params;

        $rParams = array_flip($this->queue[$method]['required']);
        $oParams = $this->queue[$method]['optional'];

        $mParams = array_merge($rParams, $oParams);

        if (count($params) < count($mParams)) {
            $params = array_merge($oParams, $params);
        }

        $sortKeys = array_flip(array_keys($mParams));

        $params = array_merge($sortKeys, $params);

        return $params;
    }

    /**
     * Builds response object
     *
     * @param $type int - 0|1 (error|success)
     * @param $data mixed - result array or error object
     * @param $id mixed - response id (corresponds to request id if set)
     * @return mixed - JSON-RPC Response object
     */
    private function buildResponse($type, $data, $id)
    {
        $response = array();
        $response['jsonrpc'] = '2.0';

        if ($type === 0) {
            $response['error'] = $data;
        } elseif ($type === 1) {
            $response['result'] = $data;
        }

        $response['id'] = $id;

        return (object)$response;
    }

    /**
     * Processes incoming JSON-RPC request,
     * validates batch JSON-RPC Request
     *
     * @param $request string - JSON-RPC Request
     * @return mixed - JSON-RPC Response object
     */
    public function process($request)
    {
        $response = null;
        $request = json_decode($request);

        try {
            if (is_array($request)) {
                $this->validateBatchJson($request);
                $this->validateBatchRequest($request);

                $response = array();

                foreach ($request as $iRequest) {
                    if (($result = $this->call($iRequest)) !== null) {
                        $response[] = $result;
                    }
                }
            } else {
                $response = $this->call($request);
            }
        } catch (Exception $e) {
            $error = new StdClass();

            $error->code = $e->getCode();
            $error->message = $e->getMessage();

            $response = $this->buildResponse(0, $error, null);
        }

        if ($response !== null && !empty($response)) {
            $response = json_encode($response);
            return $response;
        }
    }

    /**
     * Validates single JSON-RPC Request object,
     * calls corresponding service method
     *
     * @param $request mixed - JSON-RPC Request object
     * @return mixed - JSON-RPC Response object
     */
    public function call($request)
    {
        $response = null;
        $params = array();

        try {
            $this->validateJson($request);
            $this->validateRequest($request);

            $method = $request->method;

            $service = $this->validateMethod($method);

            $this->enqueue($service, $method);

            $this->areParamsExpected($request);

            if (isset($request->params)) {
                $params = $request->params;

                if (is_array($params)) {
                    $this->validatePosParams($method, $params);
                } elseif (is_object($params)) {
                    $this->validateNamedParams($method, $params);
                    $params = $this->sortNamedParams($method, $params);
                }
            }

            $result = call_user_func_array(array($service, $method), $params);

            if (isset($request->id)) {
                $response = $this->buildResponse(1, $result, $request->id);
            }
        } catch (Exception $e) {
            $error = new StdClass();

            $error->code = $e->getCode();

            if ($error->code > -32000) {
                $error->message = 'Application error.';
                $error->data = $e->getMessage();
            } else {
                $error->message = $e->getMessage();
            }

            $notRequestId = (!is_object($request) || !isset($request->id));
            $isRespondable = ($e instanceof JsonRpcInvalidJsonException
                            || $e instanceof JsonRpcInvalidRequestException);

            if ($notRequestId && $isRespondable) {
                $response = $this->buildResponse(0, $error, null);
            } elseif (isset($request->id)) {
                $response = $this->buildResponse(0, $error, $request->id);
            }
        }

        return $response;
    }
}

?>