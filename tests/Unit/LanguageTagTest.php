<?php

declare(strict_types=1);

namespace MultilingualCore\Tests\Unit;

use InvalidArgumentException;
use MultilingualCore\Domain\LanguageTag;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class LanguageTagTest extends TestCase
{
	#[DataProvider('validTags')]
	public function testNormalizesSupportedTags(string $input, string $expected): void
	{
		$this->assertSame($expected, (new LanguageTag($input))->value());
	}

	/** @return iterable<string, array{string,string}> */
	public static function validTags(): iterable
	{
		yield 'primary' => array('FA', 'fa');
		yield 'region' => array('en_gb', 'en-GB');
		yield 'script and region' => array('zh-hant-tw', 'zh-Hant-TW');
	}

	public function testRejectsPathLikeInput(): void
	{
		$this->expectException(InvalidArgumentException::class);
		new LanguageTag('../fa');
	}
}

