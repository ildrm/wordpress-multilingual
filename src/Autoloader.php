<?php

declare(strict_types=1);

namespace MultilingualCore;

final class Autoloader {

	public static function register( string $baseDirectory ): void {
			spl_autoload_register(
				static function ( string $className ) use ( $baseDirectory ): void {
					$prefix = __NAMESPACE__ . '\\';
					if ( ! str_starts_with( $className, $prefix ) ) {
						return;
					}

					$relative = substr( $className, strlen( $prefix ) );
					$file     = $baseDirectory . str_replace( '\\', '/', $relative ) . '.php';
					if ( is_readable( $file ) ) {
						require_once $file;
					}
				}
			);
	}
}
