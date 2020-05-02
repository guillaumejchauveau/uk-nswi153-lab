<?php

// sleep(1);
define('FILES_DIR', __DIR__ . '/files');


function http_error($message, $code = 400)
{
	http_response_code($code);	// bad request
	header("Content-type: text/plain");
	echo "$message\n";
	exit;
}


function http_response($json, $code = 200)
{
	http_response_code($code);
	header("Content-type: application/json");
	echo json_encode($json);
	exit;
}


function safe_get($array, $key, $default = null)
{
	return (isset($array[$key])) ? $array[$key] : $default;
}


function is_name_valid($fileName)
{
	return preg_match('/^[-_a-zA-Z0-9][-_a-zA-Z0-9.]*$/', (string)$fileName);
}


function get_files()
{
	return array_values(array_filter(scandir(FILES_DIR), 'is_name_valid'));
}


function move($oldName, $newName)
{
	$old = FILES_DIR . "/$oldName";
	$new = FILES_DIR . "/$newName";

	// Old must exist and new must not ...
	if (!file_exists($old)) return false;
	if (file_exists($new)) return false;

	return rename($old, $new);
}


try {
	if (strtoupper($_SERVER['REQUEST_METHOD']) == 'POST') {
		// Post method renames one file 
		$oldName = safe_get($_POST, 'old');
		$newName = safe_get($_POST, 'new');
		if (!is_name_valid($oldName) || !is_name_valid($newName))
			http_error("The script excepts valid 'old' and 'new' parameters.", 400);
		if (!move($oldName, $newName))
			http_error("The file cannot be renamed.", 400);
	}

	// Anyhow, return the list of files ...
	http_response((object)[
		'files' => get_files()
	]);
}
catch (Exception $e) {
	http_error($e->getMessage(), 500);
}
