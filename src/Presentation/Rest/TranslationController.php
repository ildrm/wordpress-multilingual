<?php

declare(strict_types=1);

namespace MultilingualCore\Presentation\Rest;

use InvalidArgumentException;
use MultilingualCore\Application\TranslationService;
use MultilingualCore\Application\UrlGenerator;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

final class TranslationController {

	private const NAMESPACE = 'multilingual-core/v1';

	public function __construct(
		private readonly TranslationService $translations,
		private readonly UrlGenerator $urls
	) {
	}

	public function register(): void {
		register_rest_route(
			self::NAMESPACE,
			'/objects/(?P<type>[a-z0-9_-]+)/(?P<id>\d+)/translations',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'translations' ),
				'permission_callback' => static fn (): bool => current_user_can( 'mlc_translate' ),
				'args'                => array(
					'type' => array(
						'type'     => 'string',
						'required' => true,
						'pattern'  => '^[a-z0-9_-]+$',
					),
					'id'   => array(
						'type'     => 'integer',
						'required' => true,
						'minimum'  => 1,
					),
				),
			)
		);
		register_rest_route(
			self::NAMESPACE,
			'/url',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'url' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'url'      => array(
						'type'     => 'string',
						'format'   => 'uri',
						'required' => true,
					),
					'language' => array(
						'type'      => 'string',
						'required'  => true,
						'maxLength' => 35,
					),
				),
			)
		);
	}

	public function translations( WP_REST_Request $request ): WP_REST_Response {
		return new WP_REST_Response(
			$this->translations->forObject( (string) $request['type'], (int) $request['id'] )
		);
	}

	public function url( WP_REST_Request $request ): WP_REST_Response|\WP_Error {
		try {
			return new WP_REST_Response(
				array( 'url' => $this->urls->forUrl( esc_url_raw( (string) $request['url'] ), sanitize_text_field( (string) $request['language'] ) ) )
			);
		} catch ( InvalidArgumentException $exception ) {
			return new \WP_Error( 'mlc_invalid_url_origin', $exception->getMessage(), array( 'status' => 400 ) );
		}
	}
}
