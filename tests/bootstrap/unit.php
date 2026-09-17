<?php

declare(strict_types=1);

$autoload = dirname(__DIR__, 2) . '/vendor/autoload.php';
if (! is_readable($autoload)) {
	throw new RuntimeException('Run composer install before the unit tests.');
}
require_once $autoload;

