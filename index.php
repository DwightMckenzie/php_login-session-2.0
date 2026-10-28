<?php
  /**
   * first page seen
   * Hey there this is index branch responsing
   */

	// start session
	session_start();

	// require fro the login info
	require 'helpers/url.php';
	require 'helpers/auth.php';
	
	
	isLoggedIn();
		
	header('Location: login.php');
	exit;


?>