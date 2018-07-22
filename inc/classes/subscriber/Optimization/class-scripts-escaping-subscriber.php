<?php
namespace WP_Rocket\Subscriber\Optimization;

use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Handles escaping of closing HTML tags in inline scripts to prevent them from being removed by DOMDocument
 *
 * @since 3.1
 * @author Remy Perona
 */
class Scripts_Escaping_Subscriber implements Subscriber_Interface {
	/**
	 * @inheritDoc
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_buffer' => [
				[ 'escape_html_in_scripts', 1 ],
				[ 'unescape_html_in_scripts', 20 ],
			],
		];
	}

	/**
	 * Escapes HTML closing tags contained in inline scripts to prevent them from being removed by DOMDocument
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param string $html HTML content.
	 * @return string
	 */
	public function escape_html_in_scripts( $html ) {
		$html = preg_replace( '/(<noscript[^>]*?>.*<\/noscript>)/msU', "<!--$0-->", $html );

		preg_match_all( '/<script[^>]*?>(.*)<\/script>/msU', $html, $matches );

		if ( empty( $matches[1] ) ) {
			return $html;
		}

		foreach ( $matches[1] as $k => $match ) {
			if ( empty( $match ) ) {
				continue;
			}

			$match = str_replace( '<\/', '<#\/', $match );
			$match = str_replace( '</', '<\/', $match );
			$html  = str_replace( $matches[1][ $k ], $match, $html );
		}

		return $html;
	}

	/**
	 * Unescapes HTML closing tags contained in inline scripts once we're done with DOMDocument
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param string $html HTML content.
	 * @return string
	 */
	public function unescape_html_in_scripts( $html ) {
		preg_match_all( '/<script[^>]*?>(.*)<\/script>/msU', $html, $matches );

		if ( empty( $matches[1] ) ) {
			return $html;
		}

		foreach ( $matches[1] as $k => $match ) {
			if ( empty( $match ) ) {
				continue;
			}

			$match = str_replace( '<\/', '</', $match );
			$match = str_replace( '<#\/', '<\/', $match );
			$html  = str_replace( $matches[1][ $k ], $match, $html );
		}
		$html = str_replace( '<!--<noscript>', '<noscript>', $html );
		$html = str_replace( '</noscript>-->', '</noscript>', $html );

		return $html;
	}
}