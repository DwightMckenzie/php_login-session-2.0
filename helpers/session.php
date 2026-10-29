<?php

	/**
	 * verify and set session
	 */

	// strict values
	declare(strict_types=1);

	// patch - 1.0
	function start_secure_session(): void
	{
		if (session_status() === PHP_SESSION_ACTIVE) {
			return;
		}

		// what is the protocol
		$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
				|| (($_SERVER['SERVER_PORT'] ?? null) == 443);

		session_set_cookie_params([
			'lifetime' => 0,
			'path'     => '/',
			'secure'   => $isHttps,
			'httponly' => true,
			'samesite' => 'Lax',
		]);

		ini_set('session.use_strict_mode', '1');
		session_start();
	}

	/** HTML-escape output */
	function e(?string $value): string
	{
		return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
	}

	/** One CSRF token per session */
	function csrf_token(): string
	{
		if (empty($_SESSION['csrf'])) {
			$_SESSION['csrf'] = bin2hex(random_bytes(32));
		}
		return $_SESSION['csrf'];
	}

	function csrf_valid(?string $token): bool
	{
		return is_string($token)
			&& isset($_SESSION['csrf'])
			&& hash_equals($_SESSION['csrf'], $token);
	}