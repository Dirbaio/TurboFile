<?php

function fixPath($inputpath)
{
	$path = array();

	$input = explode("/", $inputpath);
	foreach($input as $dir)
	{
		if($dir == "." || $dir == "")
			{}
		else if($dir == "..")
			array_pop($path);
		else
			array_push($path, $dir);
	}
	
	return (count($path)?'/':'').implode("/", $path);
}

function redirect($url)
{
	header("Location: ".$url);
	die();
}

function isHttps()
{
	return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']) || $_SERVER["SERVER_PORT"] == 443;
}

function json($data)
{
	header('Content-Type: application/json');
	echo json_encode($data);
	die();
}

// ACLs

//Access level: 0 = nothing, 1 = view, 2 = edit
require(__DIR__.'/auth.php');

// Returns whether the given username matches against the list
function accessMatch($username, $list)
{
	if(!is_array($list))
		return false;

	if(in_array('any', $list))
		return true;

	if($username == '')
		return in_array('guests', $list);

	// For registered users
	return
		in_array('users', $list) ||
		in_array('user:'.$username, $list);
}

function accessLevelForPath($path)
{
	global $config;

	if(!is_dir($config['files'].$path)) return 2;
	if(!is_file($config['files'].$path.'/.acl.json')) return 2;

	$acl = file_get_contents($config['files'].$path.'/.acl.json');
	$acl = json_decode($acl, true);

	// If can't parse ACL, fail just in case.
	if(!$acl)
		return 0;

	// Username, or empty string if not logged in.
	$username = Auth::getUsername();

	// Note: having write permission implies read permission.
	if(isset($acl['write']) && accessMatch($username, $acl['write']))
		return 2;

	if(isset($acl['read']) && accessMatch($username, $acl['read']))
		return 1;

	return 0;
}

function accessLevel($path)
{
	$input = explode("/", $path);
	$path = '';

	$result = 2;

	$result = min($result, accessLevelForPath($path));

	foreach($input as $dir) {
		if($dir == '' || $dir == '.' || $dir == '..')
			continue;

		$path .= '/'.$dir;
		$result = min($result, accessLevelForPath($path));
	}
	return $result;
}




function system_extension_mime_types() {
	# Returns the system MIME type mapping of extensions to MIME types, as defined in /etc/mime.types.
	$out = array();
	$file = fopen('/etc/mime.types', 'r');
	while(($line = fgets($file)) !== false) {
		$line = trim(preg_replace('/#.*/', '', $line));
		if(!$line)
			continue;
		$parts = preg_split('/\s+/', $line);
		if(count($parts) == 1)
			continue;
		$type = array_shift($parts);
		foreach($parts as $part)
			$out[$part] = $type;
	}
	fclose($file);
	$out['js'] = 'text/javascript';
	return $out;
}

function system_extension_mime_type($file) {
	# Returns the system MIME type (as defined in /etc/mime.types) for the filename specified.
	#
	# $file - the filename to examine
	static $types;
	if(!isset($types))
		$types = system_extension_mime_types();
	$ext = pathinfo($file, PATHINFO_EXTENSION);
	if(!$ext)
		$ext = $file;
	$ext = strtolower($ext);
	return isset($types[$ext]) ? $types[$ext] : null;
}

function system_mime_type_extensions() {
	# Returns the system MIME type mapping of MIME types to extensions, as defined in /etc/mime.types (considering the first
	# extension listed to be canonical).
	$out = array();
	$file = fopen('/etc/mime.types', 'r');
	while(($line = fgets($file)) !== false) {
		$line = trim(preg_replace('/#.*/', '', $line));
		if(!$line)
			continue;
		$parts = preg_split('/\s+/', $line);
		if(count($parts) == 1)
			continue;
		$type = array_shift($parts);
		if(!isset($out[$type]))
			$out[$type] = array_shift($parts);
	}
	fclose($file);
	return $out;
}

function system_mime_type_extension($type) {
	# Returns the canonical file extension for the MIME type specified, as defined in /etc/mime.types (considering the first
	# extension listed to be canonical).
	#
	# $type - the MIME type
	static $exts;
	if(!isset($exts))
		$exts = system_mime_type_extensions();
	return isset($exts[$type]) ? $exts[$type] : null;
}


