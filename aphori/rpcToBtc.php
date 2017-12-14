<html>
<head></head>
<body>
hello??
<?php
// require_once  "jsonrpcphp/includes/jsonRPCClient.php";

// currently local is at 205.149.157.12
// now 205.149.147.2
// $bcc = new jsonRPCClient('http://zonker:glorp@205.149.147.2:8332/');
// $bcc = new jsonRPCClient('http://zonker:glorp@www.repeatingshadow.mini:8332/');
// $bcc = new jsonRPCClient('zonker:glorp@127.0.0.1:8332/');
// need to set rpcallowip to repeatingshaodw.com at 63.247.139.244
// seems not to work. Perhaps open port 8332 ??
// ok, I tried that on the router.
// odd, 192.168.1.1 doesnt connect, till I turned off the named computer
// some paulbunyan messing about, spect
// now recompied bitcoind, put in rcp port in the upnp sections of net.cpp


 /***
  echo "<pre>\n";
  print_r($bcc->getinfo()); echo "\n";
  echo "Received: ".$bcc->getreceivedbylabel("Your Address")."\n";
  echo "</pre>";
****/

$client = stream_socket_client("tcp://205.149.147.2:8332",$erno,$erms);
if ($client === false) {
	echo $erms;
	echo "die</body></html>";
	die();
}

$auths = base64_encode("zonker:glorp");
$json = '{"method":"getinfo","params":[]}';
$len = strlen($json);

// fwrite($client,"GET / \r\n".
fwrite($client,"BABE / \r\n".
"Authorization: Basic ".$auths."\r\n".
"Content-Length: ".$len."\r\n".
"\r\n".
$json.
"\r\n\r\n"
);

echo stream_get_contents($client);
fclose($client);


?>
</body>
</html>