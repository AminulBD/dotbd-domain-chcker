<?php

use AminulBD\DotBD\Finder;

require_once __DIR__ . '/../vendor/autoload.php';

$finder = new Finder;

$result = $finder->name('example')->ext('.com.bd')->check();

print_r([
	'domain' => $result->domain(),
	'eligible' => ($result->eligible() ? 'Yes' : 'NO'),
	'whois' => $result->whois(), //  if registered.
	'available' => $result->available(),
]);
