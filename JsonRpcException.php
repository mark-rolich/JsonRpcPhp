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
    public function __construct($message = '', $code = 0)
    {
        parent::__construct('Parse error.', -32700);
    }
}

class JsonRpcInvalidRequestException extends Exception
{
    public function __construct($message = '', $code = 0)
    {
        parent::__construct('Invalid Request.', -32600);
    }
}

class JsonRpcInvalidMethodException extends Exception
{
    public function __construct($message = '', $code = 0)
    {
        parent::__construct('Method not found.', -32601);
    }
}

class JsonRpcInvalidParamsException extends Exception
{
    public function __construct($message = '', $code = 0)
    {
        parent::__construct('Invalid params.', -32602);
    }
}

class JsonRpcApplicationException extends Exception
{
    public function __construct($message, $code = 0)
    {
        parent::__construct($message, $code);
    }
}
?>