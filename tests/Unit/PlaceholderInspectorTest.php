<?php

declare(strict_types=1);

namespace MultilingualCore\Tests\Unit;

use MultilingualCore\Domain\PlaceholderInspector;
use PHPUnit\Framework\TestCase;

final class PlaceholderInspectorTest extends TestCase
{
	public function testAcceptsReorderedProtectedPlaceholders(): void
	{
		$result = (new PlaceholderInspector())->compare('Hello %1$s, open {{url}}', '{{url}} را باز کنید، %1$s');
		$this->assertTrue($result['valid']);
	}

	public function testReportsMissingAndAddedPlaceholders(): void
	{
		$result = (new PlaceholderInspector())->compare('Hello %s', 'سلام {{name}}');
		$this->assertFalse($result['valid']);
		$this->assertSame(array('%s'), $result['missing']);
		$this->assertSame(array('{{name}}'), $result['added']);
	}
}

