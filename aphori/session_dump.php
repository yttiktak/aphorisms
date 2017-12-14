<?php
	session_start();
	function to_hex($data){return strtoupper(bin2hex($data));};
	$cipmet = openssl_get_cipher_methods(true);
	var_dump($cipmet);
	var_dump($_SESSION);
	
?>