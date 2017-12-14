<?php 
	session_start();    
	require_once  '../php-cryptlib/lib/CryptLib/CryptLib.php';

	use CryptLib\Cipher\Factory as CipherFactory;
	$factory = new \CryptLib\Random\Factory;
	$generator = $factory->getLowStrengthGenerator();
	$hash = $generator->generateString(32,'0123456789ABCDEF');
	$iv = $generator->generateString(32,'0123456789ABCDEF');	

	$hashs = array();
	for ($i=0; $i<5; $i++) {
		$hashs[] = $generator->generateString(32,'0123456789ABCDEF');
	}

	$_SESSION['hashs'] = $hashs;
	$_SESSION['iv'] = $iv;
?>

<html> 
<head>
   <META http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>buy entertaining aphorisims with bitcoin</title>
<!-- 1.10.2 gronk on ipad ?? -->
<script type="text/javascript" src="../Scripts/jquery-2.1.0.js">
</script>
<script type="text/javascript" src="../sjcl/sjcl.js">
</script>
<script type="text/javascript" src="entertain.js?v=0.01113">
</script>

<style>
#svg_contents {
 padding:5px;
 margin:10px;
}
#blockchain {
 position:relative;
 height:100px;
 outline:1px solid grey;
}
#block_train {
 position:absolute;
 bottom:0px;
}
.block_car {
 width:120px;
 height:37px;
 padding:5px;
 margin:5px;
 outline:1px solid black;
 float:right;
}
</style>
</head>

 <body>
<h1>Oh dear. Why are you here? This is very much under construction!!</h1>
<h2>How embarrasing!</h2>
<h3>the idea is, you pay -anything- to the bitcoin address. The transaction gets animated, showing the transaction
on its way to the block chain, and the progress of the block chain once it is included. In addition, a lookout is posted 
for bandits that might spend any of the transaction's inputs before it gets to the blockchain. And finally, as this all progresss, 
a random aphorism ('still, ya gotta laugh. Never on a Sunday .. etc) is decoded and drawn in svg. 
</h3>
<div>So that is why all the websocket requests to your server, that normally would be, oh, once a month lets be realistic</div>
<div id="pay_box">pay up! <span id="address">13A5E4tkxFQ93eSPpMViaMT3gSTeRL4WE9</span></div>
<div id="options">
<a href="?confirm=0" class="testbut" data-confirm="0">confirm 0</a>
<a href="?confirm=1" class="testbut" data-confirm="1">confirm 1</a>
<a href="?confirm=2" class="testbut" data-confirm="2">confirm 2</a>
<a href="?confirm=3" class="testbut" data-confirm="3">confirm more</a>
<a href="?confirm=4" class="testbut" data-confirm="4">confirm more</a>
<a href="?confirm=5" class="testbut" data-confirm="5">confirm more</a>
<a href="?confirm=6" class="testbut" data-confirm="6">confirm more</a>
watch:<a href="?watch" id="watchbut" data-watch="13A5E4tkxFQ93eSPpMViaMT3gSTeRL4WE9">13A5E4tkxFQ93eSPpMViaMT3gSTeRL4WE9</a>
</div>
<div id="blockchain">
	<div>blocks</div>
	<div id="block_train">
		<div class="block_car"></div>
	</div>
</div>

<div id="errors">
</div>
<div id="transactions">
</div>
<div id="output">
</div>


<div id="svg_contents">
<svg version="1.1" id="svg1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 width="545.315px" height="475.346px" viewBox="0 0 545.315 475.346" style="enable-background:new 0 0 545.315 475.346;"
	 xml:space="preserve">
<g>
<path style="fill:none;stroke:#FF00FF;" 
d="M19.666,6.609
c2.771-9.769, 6.008-6.557, 6.01,0.788
c0.003,13.71-4.46,32.661-8.35,45.506	
C14.676,61.652,7.163,84.516,2.78,87.605c-4.111,2.897-1.74-7.192-0.037-14.16
c4.365-17.867,14.968-45.401,22.561-56.018
c3.247-4.537,8.523-9.173,9.158-0.635
c0.588,7.895-4.734,25.789-7.115,33.5
c-2.522,8.165  -14.865,38.873  -5.871,38.739
c3.084-0.046,  9.542-11.615,  12.746-15.014
c4.423-4.688,  9.789-10.391,  14.125-12.417
c-5.099,3.309-19.003,19.023-18.789,35.641
c0.134,10.343,17.247-9.167,19.851-22.522
c0.757-3.882,  1.006-15.96  -2.044-11.05
c-2.147,13.459,  4.312,5.875,  4.71,6.134
c6.787,4.413,  14.51,5.502,  23.124-6.559
c-2.537,3.802 -14.51,24.662  -13.206,32.187
"/>
</g>
</svg>

</div>

 </body>
</html>