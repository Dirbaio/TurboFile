<?php

require __DIR__.'/config.php';
require __DIR__.'/lib.php';

$path_unsafe = $_SERVER['DOCUMENT_URI'];
$path = fixPath($path_unsafe);

if(is_dir($config['files'].$path))
	$path .= '/';

if($path !== $path_unsafe)
	redirect($path);

if(preg_match('#^/_turbofile_api/(\w+)$#', $path, $matches))
{
	$input = json_decode(file_get_contents('php://input'), true);
	if(json_last_error() !== JSON_ERROR_NONE)
		$input = $_POST;

	foreach($_GET as $key => $value)
		$input[$key] = $value;

	require __DIR__.'/api/'.$matches[1].'.php';
}
else {
	if(accessLevel($path) < 1)
		die("NOPE");
	
	if(is_dir($config['files'].$path)) {
		if(is_file($config['files'].$path.'index.php'))
			header("X-Accel-Redirect: /_files".$path."index.php");
		else if(is_file($config['files'].$path.'index.html'))
			header("X-Accel-Redirect: /_files".$path."index.html");
		else if(is_file($config['files'].$path.'index.htm'))
			header("X-Accel-Redirect: /_files".$path."index.htm");
		else
			require(__DIR__.'/page.php');
	}
	else {
		// Setting Content-Type to empty will cause nginx
		// to set the correct type when doing the accel redirect
		header("Content-Type: ");
		header("X-Accel-Redirect: /_files".$path);
	}
}
