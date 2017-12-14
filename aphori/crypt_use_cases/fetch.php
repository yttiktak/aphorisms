<?php 
	
	include "tumblr.php-master/vendor/autoload.php";
	session_start();
	include "../../Outside/tumblrNppCreds.php";
	use Tumblr\API as TAPI;

     $nonce = str_pad(mt_rand(0,32000),6,'N');
     $_SESSION['nonce'] = $nonce;

	$res=openssl_pkey_new();
	// Get private key
	openssl_pkey_export($res, $privatekey);
	$_SESSION['privatekey'] = $privatekey;
	// Get public key
	$keyDetails=openssl_pkey_get_details($res);
	$publickey=$keyDetails["key"];

	function to_hex($data)
	{
    		return strtoupper(bin2hex($data));
	}

	function emit_password_fields() {
	?>
		password: <input id="password" type="text" name="password" value="">
		<span class="argh hidden"></span><br>
		<input type="hidden" name="hash" value="browns">
	<?php
	}

	function emit_name_password_form() {
	?>
		<form id="password_form" 
		class="password_form" 
		action="fetchResponder.php" 
		method="post" 
		target="debug"
		>
			name: <input id="user_name" type="text" name="user_name" value=""><br>
			<?php emit_password_fields(); ?>
		  	<input type = "Submit" name="submit" ><br>
			<input type="hidden" name="act" value="password_form">
		</form>
	<?php
	}

	function emit_paypal_info_form() {
	?>
		<form id="paypal_info_form" 
		class="paypal_info_form" 
		action="fetchResponder.php"
		method="post" 
		target="debug"
		>
			paypal_email_id: <input id="paypal_id" type="text" name="paypal_id" value=""><br>
			paypal_biz_id: <input id="paypal_biz" type="text" name="paypal_biz" value=""><br>
			<?php emit_password_fields(); ?>
			<div id="explain_pp_form">
				If these are blank or incorrect, enter new values and your password to change them.
			</div>
			<input type="hidden" name="user_name" value="filled_by_ajax">
			<input type="hidden" name="act" value="pp_info_form">
		  	<input type = "Submit" name="submit" ><br>
		</form>
	<?php
	}

	$client = new TAPI\Client($bobbi_token,$bobbi_secret);

	$requestHandler = $client->getRequestHandler();
	$requestHandler->setBaseUrl('http://www.tumblr.com/');

	$req = $requestHandler->request(
	 'POST', 
	 'oauth/request_token', 
	 array('oauth_callback'=>'http://www.repeatingshadow.mini/mySQL2tumblr/catch.php')
	);

	$failed = ($req->status != 200);

	if (!$failed) {
		$out = $req->body;
		$data = array();
		parse_str($out, $data);
		$_SESSION['token']=$data['oauth_token'];
		$_SESSION['secret']=$data['oauth_token_secret'];
	}

	$dbase = new mysqli("localhost", $dbuser, $dbpw, "button_up");
	if ($dbase->connect_errno) {
   		echo "<!-- Failed to connect to MySQL: " . 
		  $dbase->connect_error.
		 "-->\n";
		$failed = true;
	}

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
 <head>
   <META http-equiv="Content-Type" content="text/html; charset=utf-8">
   <title>Fetch Authorization From Tumblr</title>

    <!-- script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"--->

	<script type="text/javascript" src="../Scripts/jquery-1.10.2.min.js">
     </script>
	<script type="text/javascript" src="../Scripts/jquery-migrate-1.2.1.min.js">
	</script>
	<script type="text/javascript" src="../Scripts/md5.js">
	</script>
	<script type="text/javascript" src="../Scripts/jsbn.js">
     </script>  
	<script type="text/javascript" src="../Scripts/prng4.js">
	</script>
	<script type="text/javascript" src="../Scripts/rng.js">
	</script>  
	<script type="text/javascript" src="../Scripts/rsa.js">
	</script>    
	<script type="text/javascript" src="../Scripts/fetch.js">
	</script>

   <link rel="stylesheet" type="text/css" href="../Styles/fetch.css" />

 </head>

 <body>



<?php if ($failed) { ?>
	<div class="failure"> Tumblr or mySql do not like me right now 
		<div> probably an error in id numbers </div>
	</div>
	</body>
	</html>
<?php 
	die();
} ?>

	<div id="password_form_block">
		<?php emit_name_password_form(); ?>
	</div>

	<div id="paypal_info_block">
		<?php emit_paypal_info_form(); ?>
		<div id="paypal_button_authorized_block">
			<div>button making</div>
			<div id="paypal_button_authorized_message">has not been authorized by this account</div>
		</div>
	</div>

	<div id="authorize_and_pitch" class="require_password">
	<a href="http://www.tumblr.com/oauth/authorize?oauth_token=<?php 
	  echo $data['oauth_token']; ?>" 
	  target="tumblr_auth"
	  id = "authorize_button"
	>authorize: http://www.tumblr.com/oauth/authorize?oauth_token=<?php 
	  echo $data['oauth_token']; ?> </a>
	<br>
	</div>


	<script>
    		nonce = '<?php echo $nonce;?>';
		rsa = new RSAKey();
		rsa.setPublic('<?php echo to_hex($keyDetails['rsa']['n']) ?>',
		 '<?php echo to_hex($keyDetails['rsa']['e']) ?>'
		);
	</script>


 </body>
</html>