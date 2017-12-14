<?php 
	// version 0.02
	session_start();

	include "../../Outside/tumblrNppCreds.php";
	include "../../Outside/Sanitize.class.php";
	include_once "paypalfuncs.php";

	$laundry = new Sanitize();

	$jeo = array('yad_yadada'=>'hey');
     $nonce = $_SESSION['nonce'];
	$privatekey = $_SESSION['privatekey'];
	$datah = $_POST['hash'];
	$data = pack('H*',$datah);
	$kh = openssl_pkey_get_private($privatekey);
	$details = openssl_pkey_get_details($kh);
	if (!openssl_private_decrypt($data,$hash,$kh)) {
		$jeo['error'] = 'openssl error';
	//	$jeo['data']=$data;
		echo json_encode( $jeo);
		die();	
	}

	$nonce_salt = substr($hash,-6);
	if ($nonce_salt != $nonce) {
		$jeo['error'] = 'nonce error';
		$jeo['nonce_found']=$nonce_salt;
	//	$jeo['nonce_expected']=$nonce;
		echo json_encode( $jeo);
// `say not safe`;// FORCE REMOVAL OF REVEALING ERROR CODES WHEN UPLOADED
		die();	
	}
	$hash = substr($hash,0,-6);// strip off the nonce, used as salt here

	function valid_name($nt) {
		global $laundry;
		$san = $laundry->sanitize($nt,Sanitize::SQL);
		if (($nt=='') | ($san!=$nt)) {
			return false;
		}
		return true;
	}

	$name = $laundry->sanitize($_POST['user_name'],Sanitize::SQL);

	if (!valid_name($name)) {
		$jeo['nottaname'] = 'not a good name';
		echo json_encode( $jeo);
		die();		
	}

	$dbase = new mysqli("localhost", $dbuser, $dbpw, "button_up");
	if ($dbase->connect_errno) {
		$jeo['error'] = 'db error c:' . $dbase->connect_error;
		echo json_encode( $jeo);
		die();
	}

	$passed = false;
	$new = true;
	$pp_set = false;
	$token_set = false;


	$query = "SELECT * FROM users WHERE name LIKE BINARY '".$name."'";
	if ( ! ($res = $dbase->query($query)) ) {
		$jeo['error'] = 'db error q';
		echo json_encode( $jeo);
		die();
	}

switch ($_POST['act']) {
// PRELIMINARIES FOR ACTS THAT REQUIRE PASSWORD
case 'password_form':		
case 'pp_info_form':
	if ( $row = $res->fetch_assoc() ) {
		$new = false;
		$dbpwh = $row["pw_hash"];
		if ($dbpwh == $hash) {
			$passed = true;
			$pp_set = (!is_null($row['pp_subject']) && !is_null($row['pp_biz_id']));
			if ($pp_set) {
				$jeo['paypal_id'] = $row['pp_subject'];
				$jeo['paypal_biz'] = $row['pp_biz_id'];
			}
			$token_set = ( !is_null($row['token']) && !is_null($row['secret']) );
			$butok = testButtonCreation($row['pp_subject'],$row['pp_biz_id']) ;
			$jeo['button_ok'] = $butok;
			$query = "UPDATE users SET pp_authorized = '".$butok."' WHERE name LIKE BINARY '".$name."'"; 
			if ( ! ($res2 = $dbase->query($query)) ) {
				$jeo['error'] = 'db error p';
				echo json_encode( $jeo);
				die();
			}
		}
		$res->free();
	}
break;
// PRELIMINARIES FOR ACTS THAT DO NOT REQUIRE PASSWORD
case 'pp_test_button_manager':
	if ( $row = $res->fetch_assoc() ) {
		$pp_set = (!is_null($row['pp_subject']) && !is_null($row['pp_biz_id']));
		if ($pp_set) {
			$user_pp_subject = $row['pp_subject'];
			$user_pp_biz = $row['pp_biz_id'];
		}
		$res->free();
	}
break;
}// two switches, this ends the first one


switch ($_POST['act']) {
case 'password_form':
	if ($new) {
		$query = "INSERT INTO users (name,pw_hash) VALUES ('".$name."','".$hash."')";
		$res = $dbase->query($query);
		$jeo['entered'] = $name;
	}
	$_SESSION['name'] = $name;

	$jeo['name'] = $name;
	$jeo['pass'] = $passed;
	$jeo['pp_set'] = $pp_set;
	$jeo['token_set'] = $token_set;
	echo json_encode( $jeo);
	die();
break;
case 'pp_info_form':
	$query = "UPDATE users SET pp_subject = '".$_POST['paypal_id']."', pp_biz_id = '".$_POST['paypal_biz'] ."' WHERE name LIKE BINARY '".$name."'";
	$res = $dbase->query($query);
	$jeo['entered'] = $name;
	echo 	json_encode( $jeo);
	die();
break;
case 'pp_test_button_manager':
	$butok = testButtonCreation($user_pp_subject,$user_pp_biz) ;
	$jeo['button_ok'] = $butok;
	echo 	json_encode( $jeo);
	die();
break;
}


?>