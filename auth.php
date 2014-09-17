<?php

// Dummy Auth class, replace with whatever you want.

class Auth
{

	// Devuelve el usuario autenticado actualmente.
	// O "" (string vacío) si no.
	public static function getUsername()
	{
		if(!self::$cached)
		{
			self::$cached = true;
			self::$username = self::_getUsername();
		}
		
		return self::$username;
	}
	
	private static $cached = false;
	private static $username = "";
	
	private static function _getPageUrl()
	{
		$https = @$_SERVER["HTTPS"] == "on";
		$pageURL = $https ? "https://" : "http://";
		if ($_SERVER["SERVER_PORT"] != ($https?443:80))
			$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		else 
			$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		return $pageURL;
	}
	
	private static function _getUsername()
	{
		return "testuser";
	}

	public static function getLoginUrl()
	{
		return "/login?href=".urlencode(self::_getPageUrl());
	}

	public static function getLogoutUrl()
	{
		return "/logout";
	}
	
	public static function redirectToLogin()
	{
		header("Location: ".self::getLoginUrl());
		die();
	}
	
	public static function redirectToLogout()
	{
		header("Location: ".self::getLogoutUrl());
		die();
	}
}
