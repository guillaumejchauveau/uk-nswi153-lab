<?php

declare(strict_types=1);

require_once(__DIR__ . '/recodex_interface.php');
require_once(__DIR__ . '/recodex_model.php');
require_once(__DIR__ . '/security_model.php');


/**
 * Helper function for safe fetching data from nested arrays/objects.
 */
function safeGet($struct, $path, $softFails = false)
{
	if (!is_array($path)) $path = [ $path ];
	foreach ($path as $step) {
		if (is_array($struct)) {
			if (!array_key_exists($step, $struct)) {
				if ($softFails) return null;
				throw new Exception("Missing index '$step' in associative array.");
			}
			$struct = $struct[$step];
		} else if (is_object($struct)) {
			if (!isset($struct->$step)) {
				if ($softFails) return null;
				throw new Exception("Missing property '$step' in an object.");
			}
			$struct = $struct->$step;
		} else {
			if ($softFails) return null;
			throw new Exception("Unable to resolve '$step', given structure is not array nor object.");
		}
	}

	if (!$softFails && !$struct) {
		throw new Exception("Target value of [" . join(', ', $path) . "] path is empty.");
	}
	return $struct;
}


/**
 * Process list of references and replace all strings in format '$ref' with their values
 * in resources array (using ref as key in the array).
 */
function fixArgsReferences(array $args, array $resources)
{
	// References have format '$id', where id is the key of the resource object.
	// If a string starts with $$, the double $$ stands for regular $ literal (not a reference).
	foreach ($args as &$arg) {
		if (strlen($arg) < 2) continue;
		if ($arg[0] !== '$') continue;
		$arg = substr($arg, 1);
		if ($arg[0] === '$') continue; // its just a @ literal

		if (!array_key_exists($arg, $resources)) {
			throw new Exception("Unresolved argument reference '@$arg'.");
		}
		$arg = $resources[$arg]; // otherwise, perform the substitution
	}
	return $args;
}


/**
 * Process yaml data and build data model objects.
 * The constructed objects are stored in associative array under their IDs.
 */
function prepareResources(array $yaml)
{
	$resources = [];
	foreach ($yaml as $id => $resource) {
		// Get construction parameters and build the object.
		$className = safeGet($resource, 'class');
		$args = fixArgsReferences(safeGet($resource, '__construct'), $resources);
		if ($className == 'Project' && count($args) > 1 && $args[1]) {
			// Special case -- create a sub-project.
			list($name, $parent) = $args;
			$obj = $parent->createSubProject($name);
		} else {
			$obj = new $className(...$args);
		}
		$resources[$id] = $obj;

		// Check if there are additional methods to be called on the object (post initialization)...
		$toCall = safeGet($resource, 'call', true); // true = soft fail
		if (!$toCall) continue;

		foreach ($toCall as $callDescriptor) {
			$method = safeGet($callDescriptor, 'method');
			$args = safeGet($callDescriptor, 'args', true);
			$args = is_array($args) ? fixArgsReferences($args, $resources) : [];
			$obj->$method(...$args);
		}
	}
	return $resources;
}


/**
 * Prepare the list of tests (expand cartesean product of resources and actions).
 */
function prepareTests($yaml, $resources)
{
	$tests = [];
	$counter = 0;
	foreach ($yaml as $test) {
		++$counter;
		if (count($test) !== 3 || empty($test['user']) || empty($test['resource']) || empty($test['action'])) {
			throw new Exception("Invalid format of test #$counter.");
		}

		$user = safeGet($resources, substr($test['user'], 1));
		$resourceList = is_array($test['resource']) ? $test['resource'] : [ $test['resource'] ];
		$actionList = is_array($test['action']) ? $test['action'] : [ $test['action'] ];

		foreach ($resourceList as $resource) {
			foreach ($actionList as $action) {
				$tests[] = (object)[
					'user' => $user,
					'resourceId' => $resource,
					'resource' => safeGet($resources, substr($resource, 1)),
					'action' => $action
				];
			}
		}
	}
	return $tests;
}


/*
 * Main Script
 */
try {
	// Load data ...
	if (empty($argv[1]) || !is_readable($argv[1])) {
		throw new Exception("Input file name must be passed on as the only argument.");
	}
	$yaml = yaml_parse_file($argv[1]);
	$resources = prepareResources(safeGet($yaml, 'resources'));
	$tests = prepareTests(safeGet($yaml, 'tests'), $resources);
}
catch (Exception $e) {
	echo "Input Error: ", $e->getMessage(), "\n";
	exit(1);
}


// Run tests.
try {
	$securityModel = new SecurityModel();
	foreach ($tests as $test) {
		echo '(', $test->user->getFullName(), ' [', $test->user->getRole(), '], ',
			$test->resourceId, ', ', $test->action, ') = ';
		$res = $securityModel->hasPermissions($test->user, $test->resource, $test->action);
		echo ($res ? 'true' : 'false'), "\n";
	}
}
catch (Exception $e) {
	echo "Test Error: ", $e->getMessage(), "\n";
	exit(2);
}
