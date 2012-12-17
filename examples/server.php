<?php
include '../JsonRpcException.php';
include '../JsonRpcServer.php';
include 'MathService.php';
include 'DataService.php';
include 'OptionalParamsService.php';

$math = new MathService();
$data = new DataService();
$optional = new OptionalParamsService();
$server = new JsonRpcServer();

$server->addService($math);
$server->addService($data);
$server->addService($optional);

$request = file_get_contents("php://input");
$result = $server->process($request);

if ($result !== null) {
    echo $result;
}
?>