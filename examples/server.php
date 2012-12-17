<?php
include '../JsonRpcException.php';
include '../JsonRpcServer.php';
include 'MathService.php';
include 'DataService.php';

$math = new MathService();
$data = new DataService();
$server = new JsonRpcServer();

$server->addService($math);
$server->addService($data);

$request = file_get_contents("php://input");
$result = $server->process($request);

if ($result !== null) {
    echo $result;
}
?>