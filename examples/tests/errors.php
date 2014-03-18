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
<title>Additional errors tests</title>
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
<h1>Additional errors tests</h1>

<?php
try {
    $request = '{}';
    test($request);
?>

<p>empty rpc call (no POST data):</p>

<pre class="example">
<?php
    $request = '';
    $expected = '{"jsonrpc": "2.0", "error": {"code": -32700, "message": "Parse error."}, "id": null}';

    echo test($request, $expected);
?>
</pre>

<p>rpc call with empty JSON string:</p>

<pre class="example">
<?php
    $request = '{}';
    $expected = '{"jsonrpc": "2.0", "error": {"code": -32600, "message": "Invalid Request."},"id": null}';

    echo test($request, $expected);
?>
</pre>

<p>no "jsonrpc" set:</p>

<pre class="example">
<?php
    $request = '{"method": "subtract", "params": {"subtrahend": 23, "minuend": 42}, "id": 1}';
    $expected = '{"jsonrpc": "2.0", "error": {"code": -32600, "message": "Invalid Request."},"id": 1}';

    echo test($request, $expected);
?>
</pre>

<p>no "method" set:</p>

<pre class="example">
<?php
    $request = '{"jsonrpc": "2.0", "params": {"subtrahend": 23, "minuend": 42}, "id": 1}';
    $expected = '{"jsonrpc": "2.0", "error": {"code": -32600, "message": "Invalid Request."},"id": 1}';

    echo test($request, $expected);
?>
</pre>

<p>invalid "params" structure:</p>

<pre class="example">
<?php
    $request = '{"jsonrpc":"2.0","method":"subtract","params":42,"id":1}';
    $expected = '{"jsonrpc": "2.0", "error": {"code": -32600, "message": "Invalid Request."},"id": 1}';

    echo test($request, $expected);

    $request = '{"jsonrpc":"2.0","method":"subtract","params":[],"id":1}';
    $expected = '{"jsonrpc": "2.0", "error": {"code": -32600, "message": "Invalid Request."},"id": 1}';

    echo test($request, $expected);

    $request = '{"jsonrpc":"2.0","method":"subtract","params":{},"id":1}';
    $expected = '{"jsonrpc": "2.0", "error": {"code": -32600, "message": "Invalid Request."},"id": 1}';

    echo test($request, $expected);
?>
</pre>

<p>incorrect parameters names:</p>

<pre class="example">
<?php
    $request = '{"jsonrpc":"2.0","method":"subtract","params":{"foo":43,"bar":42},"id":1}';
    $expected = '{"jsonrpc": "2.0", "error": {"code": -32602, "message": "Invalid params."}, "id": 1}';

    echo test($request, $expected);
?>
</pre>

<p>not passing mandatory parameters:</p>

<pre class="example">
<?php
    $request = '{"jsonrpc":"2.0","method":"subtract","params":{"subtrahend":43},"id":1}';
    $expected = '{"jsonrpc": "2.0", "error": {"code": -32602, "message": "Invalid params."}, "id": 1}';

    echo test($request, $expected);
    
    $request = '{"jsonrpc":"2.0","method":"subtract","params":{"minuend":43},"id":1}';
    $expected = '{"jsonrpc": "2.0", "error": {"code": -32602, "message": "Invalid params."}, "id": 1}';

    echo test($request, $expected);    
    
    $request = '{"jsonrpc":"2.0","method":"subtract","params":[43],"id":1}';
    $expected = '{"jsonrpc": "2.0", "error": {"code": -32602, "message": "Invalid params."}, "id": 1}';

    echo test($request, $expected);
?>
</pre>

<?php } catch (Exception $e) { ?>
<p><?php echo $e->getMessage(); ?></p>
<?php } ?>
</body>
</html>