<?php
  /**
   * Dyanaimc URL functions
   * helps avoid entering file paths directly
   * 
   */

  // application directory
  $folder = "php_secured-session-2.0";

  /** 
   * redirect to set parameter
   * 
   * @param string $path fixed file
   */
  function redirect($path) {
    global $folder; // Need this for the folder

    // check the servers protocol
    // this will help apply the redirect with an absolute URL
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
      $protocol = 'https';
    } else {
      $protocol = 'http';
    }

    header("Location: $protocol://" . $_SERVER['HTTP_HOST'] . "/" . $folder . "/" . $path);
    exit;
  }

  /**
   * redirect back to current page
   * aka: refresh/reload
   * 
   */
  function refresh() {
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
  }

	/**
   * for basic url string no redirects or refresh
   * 
   */
  function directory($path){
    global $folder; // Need this for the folder

    // check server protocols
    // this will help apply the redirect with an absolute URL
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
      $protocol = 'https';
    } else {
      $protocol = 'http';
    }

    $url = "$protocol://" . $_SERVER['HTTP_HOST'] . "/" . $folder . "/" . $path;

  }