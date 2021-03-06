	<?php

$config = array(
	'files' => $_SERVER['TURBOFILE_ROOT']
);

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
	if(accessLevel($path) < 1) {
		Auth::redirectToLogin();
	}
	
	$qs = $_SERVER['QUERY_STRING'];
	if($qs) $qs='?'.$qs;

	if(is_dir($config['files'].$path)) {
		if(is_file($config['files'].$path.'index.php'))
			header("X-Accel-Redirect: /_files".$path."index.php".$qs);
		else if(is_file($config['files'].$path.'index.html'))
			header("X-Accel-Redirect: /_files".$path."index.html".$qs);
		else if(is_file($config['files'].$path.'index.htm'))
			header("X-Accel-Redirect: /_files".$path."index.htm".$qs);
		else
			require(__DIR__.'/page.php');
	}
	else {
		// Setting Content-Type to empty will cause nginx
		// to set the correct type when doing the accel redirect
		header("Content-Type: ");
		header("X-Accel-Redirect: /_files".$path.$qs);
	}
}
