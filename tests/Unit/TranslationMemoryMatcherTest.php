<?php

declare(strict_types=1);

namespace MultilingualCore\Tests\Unit;

use MultilingualCore\Domain\TranslationMemoryMatcher;
use PHPUnit\Framework\TestCase;

final class TranslationMemoryMatcherTest extends TestCase
{
	public function testExactNormalizedMatchMayBeAutoApproved(): void
	{
		$matcher = new TranslationMemoryMatcher();
		$confidence = $matcher->confidence('Hello  world', ' hello world ');
		$this->assertSame(1.0, $confidence);
		$this->assertTrue($matcher->mayAutoApprove($confidence));
	}

	public function testFuzzyMatchCannotBeAutoApproved(): void
	{
		$matcher = new TranslationMemoryMatcher();
		$confidence = $matcher->confidence('The quick brown fox', 'The quick brown fox!');
		$this->assertGreaterThan(0.8, $confidence);
		$this->assertFalse($matcher->mayAutoApprove($confidence));
	}
}

