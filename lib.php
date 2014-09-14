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


