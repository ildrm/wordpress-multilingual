<?php

declare(strict_types=1);

namespace MultilingualCore\Tests\Unit;

use MultilingualCore\Domain\SourceFingerprint;
use PHPUnit\Framework\TestCase;

final class SourceFingerprintTest extends TestCase
{
	public function testNormalizesLineEndingsAndIncidentalWhitespace(): void
	{
		$this->assertSame(
			SourceFingerprint::fromString("Hello  world\r\nNext"),
			SourceFingerprint::fromString(" Hello world\nNext ")
		);
	}

	public function testContentChangesProduceDifferentFingerprints(): void
	{
		$this->assertNotSame(SourceFingerprint::fromString('Hello'), SourceFingerprint::fromString('Hello!'));
	}
}

