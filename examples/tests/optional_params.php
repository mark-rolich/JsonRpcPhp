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
<title>Optional parameters tests</title>
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
<h1>Optional parameters tests</h1>

<?php
try {
    $request = '{}';
    test($request);
?>

<h2>Named parameters:</h2>

<h3>Method has 3 optional arguments:</h3>

<p>no argument is passed:</p>

<pre class="example">
<?php
    $request = json_encode($client->prepare('testNamedAllOptional', null, 1));
    $expected = '{"jsonrpc": "2.0","result": ["",null,100],"id": 1}';

    echo test($request, $expected);
?>
</pre>

<p>only argument #1 is passed:</p>

<pre class="example">
<?php
    $request = json_encode($client->prepare('testNamedAllOptional', array('param1' => 'first is passed'), 1));
    $expected = '{"jsonrpc": "2.0","result": ["first is passed",null,100],"id": 1}';

    echo test($request, $expected);
?>
</pre>

<p>only argument #2 is passed:</p>

<pre class="example">
<?php
    $request = json_encode($client->prepare('testNamedAllOptional', array('param2' => 'second is passed'), 1));
    $expected = '{"jsonrpc": "2.0","result": ["","second is passed",100],"id": 1}';

    echo test($request, $expected);
?>
</pre>

<p>only argument #3 is passed:</p>

<pre class="example">
<?php
    $request = json_encode($client->prepare('testNamedAllOptional', array('param3' => 'third is passed'), 1));
    $expected = '{"jsonrpc": "2.0","result": ["",null,"third is passed"],"id": 1}';

    echo test($request, $expected);
?>
</pre>

<p>arguments #1 and #2 are passed:</p>

<pre class="example">
<?php
    $request = json_encode($client->prepare('testNamedAllOptional', array('param1' => 'first is passed', 'param2' => 'second is passed'), 1));
    $expected = '{"jsonrpc": "2.0","result": ["first is passed","second is passed",100],"id": 1}';

    echo test($request, $expected);
?>
</pre>

<p>Flip arguments:</p>

<pre class="example">
<?php
    $request = json_encode($client->prepare('testNamedAllOptional', array('param2' => 'second is passed', 'param1' => 'first is passed'), 1));
    $expected = '{"jsonrpc": "2.0","result": ["first is passed","second is passed",100],"id": 1}';

    echo test($request, $expected);
?>
</pre>

<p>arguments #1 and #3 are passed:</p>

<pre class="example">
<?php
    $request = json_encode($client->prepare('testNamedAllOptional', array('param1' => 'first is passed', 'param3' => 'third is passed'), 1));
    $expected = '{"jsonrpc": "2.0","result": ["first is passed",null,"third is passed"],"id": 1}';

    echo test($request, $expected);
?>
</pre>

<p>Flip arguments:</p>

<pre class="example">
<?php
    $request = json_encode($client->prepare('testNamedAllOptional', array('param3' => 'third is passed', 'param1' => 'first is passed'), 1));
    $expected = '{"jsonrpc": "2.0","result": ["first is passed",null,"third is passed"],"id": 1}';

    echo test($request, $expected);
?>
</pre>

<p>arguments #2 and #3 are passed:</p>

<pre class="example">
<?php
    $request = json_encode($client->prepare('testNamedAllOptional', array('param2' => 'second is passed', 'param3' => 'third is passed'), 1));
    $expected = '{"jsonrpc": "2.0","result": ["","second is passed","third is passed"],"id": 1}';

    echo test($request, $expected);
?>
</pre>

<p>Flip arguments:</p>

<pre class="example">
<?php
    $request = json_encode($client->prepare('testNamedAllOptional', array('param3' => 'third is passed', 'param2' => 'second is passed'), 1));
    $expected = '{"jsonrpc": "2.0","result": ["","second is passed","third is passed"],"id": 1}';

    echo test($request, $expected);
?>
</pre>

<p>all passed:</p>

<pre class="example">
<?php
    $request = json_encode($client->prepare('testNamedAllOptional', array('param1' => 'first is passed', 'param2' => 'second is passed', 'param3' => 'third is passed'), 1));
    $expected = '{"jsonrpc": "2.0","result": ["first is passed","second is passed","third is passed"],"id": 1}';

    echo test($request, $expected);
?>
</pre>

<p>Flip arguments:</p>

<pre class="example">
<?php
    $request = json_encode($client->prepare('testNamedAllOptional', array('param2' => 'second is passed', 'param1' => 'first is passed', 'param3' => 'third is passed'), 1));
    $expected = '{"jsonrpc": "2.0","result": ["first is passed","second is passed","third is passed"],"id": 1}';

    echo test($request, $expected);
?>
</pre>

<h3>Method has 3 arguments, #1 is required, #2 and #3 are optional</h3>

<p>only argument #1 is passed:</p>

<pre class="example">
<?php
    $request = json_encode($client->prepare('testNamedFirstRequired', array('param1' => 'first is passed'), 1));
    $expected = '{"jsonrpc": "2.0","result": ["first is passed",null,100],"id": 1}';

    echo test($request, $expected);
?>
</pre>

<p>arguments #1 and #2 are passed:</p>

<pre class="example">
<?php
    $request = json_encode($client->prepare('testNamedFirstRequired', array('param1' => 'first is passed', 'param2' => 'second is passed'), 1));
    $expected = '{"jsonrpc": "2.0","result": ["first is passed","second is passed",100],"id": 1}';

    echo test($request, $expected);
?>
</pre>

<p>arguments #1 and #3 are passed:</p>

<pre class="example">
<?php
    $request = json_encode($client->prepare('testNamedFirstRequired', array('param1' => 'first is passed', 'param3' => 'third is passed'), 1));
    $expected = '{"jsonrpc": "2.0","result": ["first is passed",null,"third is passed"],"id": 1}';

    echo test($request, $expected);
?>
</pre>

<h3>Method has 3 arguments, #1 and #2 are required, #3 is optional</h3>

<p>only arguments #1 and #2 is passed:</p>

<pre class="example">
<?php
    $request = json_encode($client->prepare('testNamedThirdOptional', array('param1' => 'first is passed', 'param2' => 'second is passed'), 1));
    $expected = '{"jsonrpc": "2.0","result": ["first is passed","second is passed",100],"id": 1}';

    echo test($request, $expected);
?>
</pre>

<pre class="example">
<?php
    $request = json_encode($client->prepare('testNamedThirdOptional', array('param1' => 'first is passed', 'param2' => 'second is passed', 'param3' => 'third is passed'), 1));
    $expected = '{"jsonrpc": "2.0","result": ["first is passed","second is passed","third is passed"],"id": 1}';

    echo test($request, $expected);
?>
</pre>

<p>Flip arguments:</p>

<pre class="example">
<?php
    $request = json_encode($client->prepare('testNamedThirdOptional', array('param2' => 'second is passed', 'param1' => 'first is passed', 'param3' => 'third is passed'), 1));
    $expected = '{"jsonrpc": "2.0","result": ["first is passed","second is passed","third is passed"],"id": 1}';

    echo test($request, $expected);
?>
</pre>

<h2>Positional parameters</h2>

<h3>Method has 3 optional arguments:</h3>

<p>no argument is passed:</p>

<pre class="example">
<?php
    $request = json_encode($client->prepare('testPositionalAllOptional', null, 1));
    $expected = '{"jsonrpc": "2.0","result": ["",null,100],"id": 1}';

    echo test($request, $expected);
?>
</pre>

<p>only argument #1 is passed:</p>

<pre class="example">
<?php
    $request = json_encode($client->prepare('testPositionalAllOptional', array('first is passed'), 1));
    $expected = '{"jsonrpc": "2.0","result": ["first is passed",null,100],"id": 1}';

    echo test($request, $expected);
?>
</pre>

<p>only arguments #1 and #2 are passed:</p>

<pre class="example">
<?php
    $request = json_encode($client->prepare('testPositionalAllOptional', array('first is passed', 'second is passed'), 1));
    $expected = '{"jsonrpc": "2.0","result": ["first is passed","second is passed",100],"id": 1}';

    echo test($request, $expected);
?>
</pre>

<?php } catch (Exception $e) { ?>
<p><?php echo $e->getMessage(); ?></p>
<?php } ?>
</body>
</html>