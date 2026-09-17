<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/src/Autoloader.php';
MultilingualCore\Autoloader::register(dirname(__DIR__) . '/src/');

use MultilingualCore\Domain\LanguageTag;
use MultilingualCore\Domain\PlaceholderInspector;
use MultilingualCore\Domain\SourceFingerprint;
use MultilingualCore\Domain\TranslationMemoryMatcher;

$assertions = 0;
$assert = static function (bool $condition, string $message) use (&$assertions): void {
	++$assertions;
	if (! $condition) {
		throw new RuntimeException($message);
	}
};

$assert('zh-Hant-TW' === (new LanguageTag('zh_hant_tw'))->value(), 'Language tags must normalize script and region.');
$assert(SourceFingerprint::fromString("A  B\r\nC") === SourceFingerprint::fromString(" A B\nC "), 'Fingerprints must normalize incidental whitespace.');
$tokens = (new PlaceholderInspector())->compare('Open %1$s at {{url}}', '{{url}} را در %1$s باز کنید');
$assert($tokens['valid'], 'Reordered placeholders must remain valid.');
$missing = (new PlaceholderInspector())->compare('Open %s', 'باز کنید');
$assert(! $missing['valid'] && array('%s') === $missing['missing'], 'Removed placeholders must be detected.');
$matcher = new TranslationMemoryMatcher();
$assert($matcher->mayAutoApprove($matcher->confidence('Exact', ' exact ')), 'Normalized exact memory must be approvable.');
$assert(! $matcher->mayAutoApprove($matcher->confidence('Exact', 'Exact!')), 'Fuzzy memory must never auto-approve.');

fwrite(STDOUT, "Domain smoke tests passed ($assertions assertions).\n");
