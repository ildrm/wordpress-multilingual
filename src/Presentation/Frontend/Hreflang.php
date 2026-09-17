<?php

declare(strict_types=1);

namespace MultilingualCore\Presentation\Frontend;

use MultilingualCore\Application\LanguageService;
use MultilingualCore\Application\TranslationService;
use MultilingualCore\Application\UrlGenerator;

final class Hreflang {

	public function __construct(
		private readonly TranslationService $translations,
		private readonly LanguageService $languages,
		private readonly UrlGenerator $urls
	) {
	}

	public function render(): void {
		if ( ! is_singular() ) {
			return;
		}
		$post = get_queried_object();
		if ( ! $post instanceof \WP_Post || 'publish' !== $post->post_status ) {
			return;
		}

		$translations = $this->translations->forObject( 'post', $post->ID );
		foreach ( $this->languages->all( null, true ) as $language ) {
			$tag = (string) $language['tag'];
			if ( ! isset( $translations[ $tag ] ) ) {
				continue;
			}
			$targetId = (int) $translations[ $tag ]['object_id'];
			if ( 'publish' !== get_post_status( $targetId ) ) {
				continue;
			}
			$url = get_permalink( $targetId );
			if ( ! is_string( $url ) || '' === $url ) {
				continue;
			}
			printf( "<link rel=\"alternate\" hreflang=\"%s\" href=\"%s\" />\n", esc_attr( $tag ), esc_url( $this->urls->forUrl( $url, $tag ) ) );
		}

		$default = $this->languages->default();
		if ( is_array( $default ) && isset( $translations[ (string) $default['tag'] ] ) ) {
			$targetId = (int) $translations[ (string) $default['tag'] ]['object_id'];
			$url      = get_permalink( $targetId );
			if ( 'publish' === get_post_status( $targetId ) && is_string( $url ) ) {
				printf( "<link rel=\"alternate\" hreflang=\"x-default\" href=\"%s\" />\n", esc_url( $this->urls->forUrl( $url, (string) $default['tag'] ) ) );
			}
		}
	}
}
