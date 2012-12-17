<?php
include '../../JsonRpcClient.php';

$url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$serverUrl = substr($url, 0, strpos($url, 'tests')) . 'server.php';

$client = new JsonRpcClient($serverUrl);

function json_prettify($str)
{
    $result = '';
    $len = strlen($str);
    $pos = 0;
    $indent = '    ';
    $prev = '';
    $newline = "\r\n";

    for ($i = 0; $i < $len; $i++) {

        if ($str[$i] === '[' || $str[$i] === '{') {
            $pos++;

            if ($prev !== ',' && $prev !== '[' && $prev !== '' && $prev !== ':') {
                $result .= $newline . str_repeat($indent, $pos - 1);
            }

            $result .= $str[$i] . $newline . str_repeat($indent, $pos);
        } elseif ($str[$i] === ':') {
            $result .= $str[$i] . ' ';
        } elseif ($str[$i] === ',') {
            $result .= $str[$i] . $newline . str_repeat($indent, $pos);
        } elseif ($str[$i] === ']' || $str[$i] === '}') {
            $pos--;
            $result .= $newline . str_repeat($indent, $pos) . $str[$i];
        } else {
            $result .= $str[$i];
        }

        $prev = $str[$i];
    }

    return $result;
}

function test($request, $expected = null)
{
    global $client;

    $result = '';
    $response = $client->rawcall($request);

    if ($expected !== null) {
        $expected = json_encode(json_decode($expected));
    }

    $result .= '--> ' . $request . "\r\n";

    if ($response !== null) {
        $result .= '<-- ' . json_prettify($response) . "\r\n";
    }

    if ($response === $expected) {
        $result .= '<strong class="pass">Pass</strong>';
    } else {
        $result .= '<strong class="fail">Fail</strong> (expecting ' . $expected . ')';
    }

    return $result;
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
        "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<title>JSON-RPC 2.0 specification examples test</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<style type="text/css">
html, body {
    font: 12px Arial
}

h1 {
    font-size: 17px
}

pre {
    border: solid 1px #ccc;
    padding: 10px;
    display: inline-block;
    *display: inline;
    zoom: 1
}

.example {
    background-color: #FFEAEA;
}

pre strong {
    display: block;
    width: auto;
    font-size: 14px;
    color: #fff;
    padding: 3px;
    margin: 10px 0
}

.pass {
    background-color: green;
}

.fail {
    background-color: red;
}
</style>
</head>
<body>
<h1>JSON-RPC 2.0 specification <a href="http://www.jsonrpc.org/specification#examples">examples</a> test</h1>

<?php
try {
    $request = '{}';
    test($request);
?>

<p>rpc call with positional parameters:</p>

<pre class="example">
<?php
    $request = '{"jsonrpc": "2.0", "method": "subtract", "params": [42, 23], "id": 1}';
    $expected = '{"jsonrpc": "2.0", "result": 19, "id": 1}';

    echo test($request, $expected);

    $request = '{"jsonrpc": "2.0", "method": "subtract", "params": [42, 23], "id": 1}';
    $expected = '{"jsonrpc": "2.0", "result": 19, "id": 1}';

    echo test($request, $expected);
?>
</pre>

<p>rpc call with named parameters:</p>

<pre class="example">
<?php
    $request = '{"jsonrpc": "2.0", "method": "subtract", "params": {"subtrahend": 23, "minuend": 42}, "id": 3}';
    $expected = '{"jsonrpc": "2.0", "result": 19, "id": 3}';

    echo test($request, $expected);

    $request = '{"jsonrpc": "2.0", "method": "subtract", "params": {"minuend": 42, "subtrahend": 23}, "id": 4}';
    $expected = '{"jsonrpc": "2.0", "result": 19, "id": 4}';

    echo test($request, $expected);
?>
</pre>

<p>a Notification:</p>

<pre class="example">
<?php
    $request = '{"jsonrpc": "2.0", "method": "update", "params": [1,2,3,4,5]}';

    echo test($request);

    $request = '{"jsonrpc": "2.0", "method": "foobar"}';

    echo test($request);
?>
</pre>

<p>rpc call of non-existent method:</p>

<pre class="example">
<?php
    $request = '{"jsonrpc": "2.0", "method": "foobar", "id": "1"}';
    $expected = '{"jsonrpc": "2.0", "error": {"code": -32601, "message": "Method not found."}, "id": "1"}';

    echo test($request, $expected);
?>
</pre>

<p>rpc call with invalid JSON:</p>

<pre class="example">
<?php
    $request = '{"jsonrpc": "2.0", "method": "foobar, "params": "bar", "baz]';
    $expected = '{"jsonrpc": "2.0", "error": {"code": -32700, "message": "Parse error."}, "id": null}';

    echo test($request, $expected);
?>
</pre>

<p>rpc call with invalid Request object:</p>

<pre class="example">
<?php
    $request = '{"jsonrpc": "2.0", "method": 1, "params": "bar"}';
    $expected = '{"jsonrpc": "2.0", "error": {"code": -32600, "message": "Invalid Request."}, "id": null}';

    echo test($request, $expected);
?>
</pre>

<p>rpc call Batch, invalid JSON:</p>

<pre class="example">
<?php
    $request = '[
  {"jsonrpc": "2.0", "method": "sum", "params": [1,2,4], "id": "1"},
  {"jsonrpc": "2.0", "method"
]';
    $expected = '{"jsonrpc": "2.0", "error": {"code": -32700, "message": "Parse error."}, "id": null}';

    echo test($request, $expected);
?>
</pre>

<p>rpc call with an empty Array:</p>

<pre class="example">
<?php
    $request = '[]';
    $expected = '{"jsonrpc": "2.0", "error": {"code": -32600, "message": "Invalid Request."}, "id": null}';

    echo test($request, $expected);
?>
</pre>

<p>rpc call with an invalid Batch (but not empty):</p>

<pre class="example">
<?php
    $request = '[1]';
    $expected = '[
  {"jsonrpc": "2.0", "error": {"code": -32600, "message": "Invalid Request."}, "id": null}
]';

    echo test($request, $expected);
?>
</pre>

<p>rpc call with invalid Batch:</p>

<pre class="example">
<?php
    $request = '[1,2,3]';
    $expected = '[
  {"jsonrpc": "2.0", "error": {"code": -32600, "message": "Invalid Request."}, "id": null},
  {"jsonrpc": "2.0", "error": {"code": -32600, "message": "Invalid Request."}, "id": null},
  {"jsonrpc": "2.0", "error": {"code": -32600, "message": "Invalid Request."}, "id": null}
]';

    echo test($request, $expected);
?>
</pre>

<p>rpc call Batch:</p>

<pre class="example">
<?php
    $request = '[
        {"jsonrpc": "2.0", "method": "sum", "params": [1,2,4], "id": "1"},
        {"jsonrpc": "2.0", "method": "notify_hello", "params": [7]},
        {"jsonrpc": "2.0", "method": "subtract", "params": [42,23], "id": "2"},
        {"foo": "boo"},
        {"jsonrpc": "2.0", "method": "foo.get", "params": {"name": "myself"}, "id": "5"},
        {"jsonrpc": "2.0", "method": "get_data", "id": "9"}
    ]';
    $expected = '[
        {"jsonrpc": "2.0", "result": 7, "id": "1"},
        {"jsonrpc": "2.0", "result": 19, "id": "2"},
        {"jsonrpc": "2.0", "error": {"code": -32600, "message": "Invalid Request."}, "id": null},
        {"jsonrpc": "2.0", "error": {"code": -32601, "message": "Method not found."}, "id": "5"},
        {"jsonrpc": "2.0", "result": ["hello", 5], "id": "9"}
    ]';

    echo test($request, $expected);
?>
</pre>

<p>rpc call Batch (all notifications):</p>

<pre class="example">
<?php
    $request = '[
        {"jsonrpc": "2.0", "method": "notify_sum", "params": [1,2,4]},
        {"jsonrpc": "2.0", "method": "notify_hello", "params": [7]}
    ]';

    echo test($request);
?>
</pre>

<?php } catch (Exception $e) { ?>
<p><?php echo $e->getMessage(); ?></p>
<?php } ?>
</body>
</html>