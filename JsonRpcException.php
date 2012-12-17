<?php
/**
* PHP implementation of JSON-RPC 2.0
* JSON-RPC Errors
* Specification: http://www.jsonrpc.org/specification
*
* @package JsonRpcPhp
* @author Mark Rolich <mark.rolich@gmail.com>
*/
class JsonRpcInvalidJsonException extends Exception
{
    public function getError()
    {
        $error = new StdClass();

        $error->code = -32700;
        $error->message = 'Parse error.';

        return $error;
    }
}

class JsonRpcInvalidRequestException extends Exception
{
    public function getError()
    {
        $error = new StdClass();

        $error->code = -32600;
        $error->message = 'Invalid Request.';

        return $error;
    }
}

class JsonRpcInvalidMethodException extends Exception
{
    public function getError()
    {
        $error = new StdClass();

        $error->code = -32601;
        $error->message = 'Method not found.';

        return $error;
    }
}

class JsonRpcInvalidParamsException extends Exception
{
    public function getError()
    {
        $error = new StdClass();

        $error->code = -32602;
        $error->message = 'Invalid params.';

        return $error;
    }
}

class JsonRpcApplicationException extends Exception
{
    public function __construct($message, $code = 0)
    {
        parent::__construct($message, $code);
    }

    public function getError()
    {
        $error = new StdClass();

        $error->code = $this->getCode();
        $error->message = 'Application error.';
        $error->data = $this->getMessage();

        return $error;
    }
}
?>