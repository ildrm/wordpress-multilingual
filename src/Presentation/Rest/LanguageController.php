<?php

declare(strict_types=1);

namespace MultilingualCore\Presentation\Rest;

use InvalidArgumentException;
use MultilingualCore\Application\LanguageService;
use RuntimeException;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

final class LanguageController {

	private const NAMESPACE = 'multilingual-core/v1';

	public function __construct( private readonly LanguageService $languages ) {
	}

	public function register(): void {
		register_rest_route(
			self::NAMESPACE,
			'/languages',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'index' ),
					'permission_callback' => '__return_true',
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create' ),
					'permission_callback' => static fn (): bool => current_user_can( 'mlc_manage_languages' ),
					'args'                => $this->schema(),
				),
			)
		);
		register_rest_route(
			self::NAMESPACE,
			'/languages/(?P<id>\d+)/default',
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'makeDefault' ),
				'permission_callback' => static fn (): bool => current_user_can( 'mlc_manage_languages' ),
				'args'                => array(
					'id'                  => array(
						'type'     => 'integer',
						'minimum'  => 1,
						'required' => true,
					),
					'impact_acknowledged' => array(
						'type'     => 'boolean',
						'required' => true,
					),
				),
			)
		);
	}

	public function index(): WP_REST_Response {
		$response = new WP_REST_Response( $this->languages->all( null, true ) );
		$response->header( 'Cache-Control', 'public, max-age=60' );

		return $response;
	}

	public function create( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		try {
			$id = $this->languages->save( $request->get_params() );

			return new WP_REST_Response( array( 'id' => $id ), 201 );
		} catch ( InvalidArgumentException $exception ) {
			return new WP_Error( 'mlc_invalid_language', $exception->getMessage(), array( 'status' => 400 ) );
		} catch ( RuntimeException $exception ) {
			return new WP_Error( 'mlc_language_conflict', $exception->getMessage(), array( 'status' => 409 ) );
		}
	}

	public function makeDefault( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		try {
			$this->languages->setDefault( (int) $request['id'], (bool) $request['impact_acknowledged'] );

			return new WP_REST_Response( array( 'updated' => true ) );
		} catch ( RuntimeException $exception ) {
			return new WP_Error( 'mlc_default_language_rejected', $exception->getMessage(), array( 'status' => 409 ) );
		}
	}

	/** @return array<string, array<string, mixed>> */
	private function schema(): array {
		return array(
			'tag'         => array(
				'type'      => 'string',
				'required'  => true,
				'minLength' => 2,
				'maxLength' => 35,
			),
			'wp_locale'   => array(
				'type'      => 'string',
				'required'  => true,
				'maxLength' => 35,
			),
			'native_name' => array(
				'type'      => 'string',
				'required'  => true,
				'maxLength' => 191,
			),
			'admin_name'  => array(
				'type'      => 'string',
				'required'  => true,
				'maxLength' => 191,
			),
			'direction'   => array(
				'type'    => 'string',
				'enum'    => array( 'ltr', 'rtl' ),
				'default' => 'ltr',
			),
			'enabled'     => array(
				'type'    => 'boolean',
				'default' => true,
			),
			'is_public'   => array(
				'type'    => 'boolean',
				'default' => true,
			),
		);
	}
}
