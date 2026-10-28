<?php
  /**
   * Dyanaimc URL functions
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

    // header("Location: $protocol://" . $_SERVER['HTTP_HOST'] . "/salary-beta-3.1" . $path); // ORIGINAL
    header("Location: $protocol://" . $_SERVER['HTTP_HOST'] . "/" . $folder . "/" . $path);
    exit;

    /**
     * other samples
     * 
     */
    // header('Location: http://localhost/salary-weekly-exp-1.1');
    // header('Location: '.$_SERVER['PHP_SELF']);
    // header('Location:'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']);

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