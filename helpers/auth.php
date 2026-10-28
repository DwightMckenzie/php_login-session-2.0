<?php

  /**
   * Revised by AI
   * 
   * 
   */

  declare(strict_types=1);

  const SESSION_IDLE_TIMEOUT = 1800; // 30 minutes

  function isLoggedIn(): bool
  {
    return ($_SESSION['logged_in'] ?? false) === true
      && !empty($_SESSION['id']);
  }

  /** Call at the top of every protected page */
  function requireLogin(): void
  {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }

    if (isset($_SESSION['last_activity'])
        && time() - $_SESSION['last_activity'] > SESSION_IDLE_TIMEOUT) {
        $_SESSION = [];
        session_destroy();
        header('Location: login.php?timeout=1');
        exit;
    }

    $_SESSION['last_activity'] = time();
  }



  /**
   * Return the authentication status 
   * 
   * @return boolean True if a user is logged in, false otherwise
   * 
   *

  function isLoggedIn() {
    // check login normally
    // if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] == '') {
    if (!isset($_SESSION['logged_in']) && $_SESSION['id'] == null) {
      // header('Location: login.php?notice="Please login first"');
      header('Location: login.php');
      // header('Location: 404.php');
      die();
    }

    // if (isset($_GET['status']) && $_GET['status'] == 'logged-in') {
    //   header('Location: hello.php');
    //   die();
    // }

    // trying to access page directly without loggin in
    // if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] == '') {
    //   header('Location: login.php?notice="Please login"');
    //   die();   
    // }

  }

  */


?>