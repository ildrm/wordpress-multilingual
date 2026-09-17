<?php

declare(strict_types=1);

namespace MultilingualCore\Presentation\Frontend;

use MultilingualCore\Application\LanguageContext;
use MultilingualCore\Application\LanguageService;
use MultilingualCore\Application\TranslationService;
use MultilingualCore\Application\UrlGenerator;

final class LanguageSwitcher {

	public function __construct(
		private readonly LanguageService $languages,
		private readonly LanguageContext $context,
		private readonly TranslationService $translations,
		private readonly UrlGenerator $urls
	) {
	}

	public function register(): void {
		add_shortcode( 'mlc_language_switcher', array( $this, 'shortcode' ) );
		register_block_type(
			MLC_PLUGIN_DIR . 'blocks/language-switcher',
			array(
				'render_callback' => array( $this, 'render' ),
			)
		);
	}

	/** @param array<string, mixed> $attributes */
	public function shortcode( array $attributes = array() ): string {
		$attributes = shortcode_atts(
			array(
				'hide_current' => false,
				'hide_missing' => true,
			),
			$attributes,
			'mlc_language_switcher'
		);

		return $this->render(
			array(
				'hideCurrent' => filter_var( $attributes['hide_current'], FILTER_VALIDATE_BOOL ),
				'hideMissing' => filter_var( $attributes['hide_missing'], FILTER_VALIDATE_BOOL ),
			)
		);
	}

	/** @param array<string, mixed> $attributes */
	public function render( array $attributes = array() ): string {
		$current      = $this->context->current();
		$currentTag   = is_array( $current ) ? (string) $current['tag'] : '';
		$translations = array();
		$objectId     = get_queried_object_id();
		if ( $objectId > 0 && is_singular() ) {
			$translations = $this->translations->forObject( 'post', $objectId );
		}

		wp_enqueue_style( 'mlc-switcher', plugins_url( 'assets/switcher.css', MLC_PLUGIN_FILE ), array(), MLC_VERSION );
		$items = array();
		foreach ( $this->languages->all( null, true ) as $language ) {
			$tag = (string) $language['tag'];
			if ( ! empty( $attributes['hideCurrent'] ) && $tag === $currentTag ) {
				continue;
			}
			$available = ! is_singular() || isset( $translations[ $tag ] );
			if ( ! $available && ! empty( $attributes['hideMissing'] ) ) {
				continue;
			}

			$url = home_url( '/' );
			if ( $available && isset( $translations[ $tag ] ) ) {
				$permalink = get_permalink( (int) $translations[ $tag ]['object_id'] );
				if ( is_string( $permalink ) ) {
					$url = $permalink;
				}
			} elseif ( ! is_singular() ) {
				$url = home_url( (string) wp_parse_url( (string) ( $_SERVER['REQUEST_URI'] ?? '/' ), PHP_URL_PATH ) );
			}
			$url   = $this->urls->forUrl( $url, $tag );
			$label = (string) $language['native_name'];
			if ( $available ) {
				$items[] = sprintf(
					'<li><a hreflang="%1$s" lang="%1$s" dir="%2$s" href="%3$s"%4$s>%5$s</a></li>',
					esc_attr( $tag ),
					esc_attr( (string) $language['direction'] ),
					esc_url( $url ),
					$tag === $currentTag ? ' aria-current="page"' : '',
					esc_html( $label )
				);
			} else {
				$items[] = sprintf( '<li><span lang="%1$s" dir="%2$s" aria-disabled="true">%3$s</span></li>', esc_attr( $tag ), esc_attr( (string) $language['direction'] ), esc_html( $label ) );
			}
		}

		if ( array() === $items ) {
			return '';
		}

		return '<nav class="mlc-switcher" aria-label="' . esc_attr__( 'Language', 'multilingual-core' ) . '"><ul>' . implode( '', $items ) . '</ul></nav>';
	}
}
