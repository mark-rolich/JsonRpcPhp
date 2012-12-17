<?php
include '../JsonRpcClient.php';

$url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$serverUrl = substr($url, 0, strpos($url, 'client.php')) . 'server.php';

$client = new JsonRpcClient($serverUrl);

try {
    // Call in context of server

    $params = new StdClass();
    $params->minuend = 42;
    $params->subtrahend = 23;

    $response = $client->subtract($params, 1);
    var_dump($response);

    $params = array('subtrahend' => 42, 'minuend' => 23);

    $response = $client->subtract($params, 2);
    var_dump($response);

    // Simple call

    $response = $client->call('subtract', array(42, 23), 3);
    var_dump($response);

    $response = $client->call('subtract', array('subtrahend' => 23, 'minuend' => 42), 4);
    var_dump($response);

    // Raw call
    $response = $client->rawcall('{"jsonrpc":"2.0","method":"subtract","params":{"subtrahend":23,"minuend":42},"id":5}');
    var_dump($response);

    // Batch call
    $requests = array();
    
    $requests[] = $client->prepare('subtract', 2, 1);
    $requests[] = $client->prepare('subtract', array(23, 52), 2);
    $requests[] = $client->prepare('subtract', array(45, 52), 3);
    $requests[] = $client->prepare('subtract', array(7, 52), 4);
    $requests[] = $client->prepare('foobar', array(7, 52), 5);

    $response = $client->callBatch($requests);
    var_dump($response);
} catch (Exception $e) {
    echo $e->getMessage();
}
?>