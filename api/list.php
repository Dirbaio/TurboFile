<?php

function getFileType($path)
{
	$mimetype = system_extension_mime_type($path);
	if($mimetype == 'text/html') $mimetype = null;

	if($mimetype == null)
		$filetype = '';
	else if(strpos($mimetype, "opendocument.text") !== FALSE)
	{
		$filetype = 'opendocument.text';
	}
	else
	{
		$filetype = substr($mimetype, 0, strpos($mimetype, '/'));
		if($filetype == 'application')
			$filetype = 'object';
	}
	return $filetype;
}

function isReloadDir($path)
{
	global $config;

	return 
		   is_file($config['files'].$path.'/'.'index.htm')
		|| is_file($config['files'].$path.'/'.'index.html')
		|| is_file($config['files'].$path.'/'.'index.php');
}

function compFileDir($a, $b)
{
	return strcmp(strtolower($a['name']), strtolower($b['name']));
}

function listDir($path)
{
	global $config;
	$list_exclude = array(".acl.json");
	
	$dirs = array();
	$files = array();
	if ($handle = @opendir($config['files'].$path))
	{
		while (false !== ($file = readdir($handle)))
		{
			if ($file == "." || $file == ".." || in_array($file, $list_exclude) || (is_dir($config['files'].$path.'/'.$file) && accessLevel($path.'/'.$file.'/') < 1))  continue;

			if (is_dir($config['files'].$path.'/'.$file)) {

				$dirs[] = array(
					'type' => 'dir',
					'name' => $file,
					'path' => $path.'/'.$file.'/',
					'link' => $path.'/'.$file.'/',
					'reload' => isReloadDir($path.'/'.$file),
				);
			}
			else {
				$filetype = getFileType($file);
				$files[] = array(
					'type' => 'file',
					'name' => $file,
					'path' => $path.'/'.$file,
					'link' => $filetype ?
						($path.'/#'.$file) :
						($path.'/'.$file),
					'filetype' => $filetype,
					'reload' => !$filetype,
				);
			}
		}
		usort($dirs, "compFileDir");
		usort($files, "compFileDir");
		closedir($handle); 
	}

	return array(
		'type' => 'dir',
		'name' => basename($path),
		'path' => $path.'/',
		'files' => array_merge($dirs, $files),
		'reload' => isReloadDir($path),
	);
}

function listFile($path)
{
	global $config;

	$filetype = getFileType($path);

	if($filetype == 'text')
		$text = file_get_contents($config['files'].$path);
	else if($filetype == 'opendocument.text')
	{	
		require("Ophir.php");
		$ophir = new \lovasoa\Ophir();
		$ophir->setConfiguration(\lovasoa\Ophir::HEADINGS,          \lovasoa\Ophir::ALL);
		$ophir->setConfiguration(\lovasoa\Ophir::LISTS,             \lovasoa\Ophir::ALL);
		$ophir->setConfiguration(\lovasoa\Ophir::TABLE,             \lovasoa\Ophir::ALL);
		$ophir->setConfiguration(\lovasoa\Ophir::FOOTNOTE,          \lovasoa\Ophir::ALL);
		$ophir->setConfiguration(\lovasoa\Ophir::LINK,              \lovasoa\Ophir::ALL);
		$ophir->setConfiguration(\lovasoa\Ophir::IMAGE,             \lovasoa\Ophir::ALL);
		$ophir->setConfiguration(\lovasoa\Ophir::NOTE,              \lovasoa\Ophir::ALL);
		$ophir->setConfiguration(\lovasoa\Ophir::ANNOTATION,        \lovasoa\Ophir::ALL);
		$ophir->setConfiguration(\lovasoa\Ophir::TABLE_OF_CONTENTS, \lovasoa\Ophir::ALL);
		$text = $ophir->odt2html($config['files'].$path);
	}
	else
		$text = '';

	return array(
		'type' => 'file',
		'filetype' => $filetype,
		'mimetype' => system_extension_mime_type($path),
		'name' => basename($path),
		'path' => $path,
		'text' => $text
	);
}

function listPath($path)
{
	global $config;

	$path = fixPath($path);
	$path = rtrim($path, '/');

	if(accessLevel($path) < 1)
		return array(
			'type' => 'nope',
			'path' => $path,
		);

	if(is_dir($config['files'].$path))
		return listDir($path);
	if(is_file($config['files'].$path))
		return listFile($path);

	return list404();
}

json(array_map('listPath', $input['paths']));