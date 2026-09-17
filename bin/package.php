<?php

declare(strict_types=1);

$root    = dirname(__DIR__);
$version = '0.1.0';
$build   = $root . '/build';
$stage   = $build . '/multilingual-core';
$zipPath = $build . '/multilingual-core-' . $version . '.zip';

if (! extension_loaded('zip')) {
	fwrite(STDERR, "The PHP zip extension is required.\n");
	exit(1);
}

if (is_dir($stage)) {
	$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($stage, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
	foreach ($iterator as $item) {
		$item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
	}
	rmdir($stage);
}
if (is_file($zipPath)) {
	unlink($zipPath);
}

$runtime = array('assets', 'blocks', 'docs', 'includes', 'languages', 'src', 'CHANGELOG.md', 'LICENSE', 'README.md', 'readme.txt', 'multilingual-core.php', 'uninstall.php');
mkdir($stage, 0775, true);
foreach ($runtime as $relative) {
	$source = $root . '/' . $relative;
	$target = $stage . '/' . $relative;
	if (is_dir($source)) {
		$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
		foreach ($iterator as $item) {
			$destination = $target . '/' . $iterator->getSubPathName();
			if ($item->isDir()) {
				is_dir($destination) || mkdir($destination, 0775, true);
			} else {
				is_dir(dirname($destination)) || mkdir(dirname($destination), 0775, true);
				copy($item->getPathname(), $destination);
			}
		}
	} elseif (is_file($source)) {
		copy($source, $target);
	}
}

$zip = new ZipArchive();
if (true !== $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
	fwrite(STDERR, "Unable to create release ZIP.\n");
	exit(1);
}
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($stage, FilesystemIterator::SKIP_DOTS));
foreach ($iterator as $item) {
	if ($item->isFile()) {
		$zip->addFile($item->getPathname(), 'multilingual-core/' . $iterator->getSubPathName());
	}
}
$zip->close();

fwrite(STDOUT, $zipPath . PHP_EOL);
