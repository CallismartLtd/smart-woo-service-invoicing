<?php
/**
 * The Sanitization class file.
 *
 * @author  Callistus Nwachukwu
 * @package Callismart\Classes
 * @version 1.1.0
 * @since   1.0.0
 */

namespace SmartWoo_REST_API;

defined( 'ABSPATH' ) || exit;

/**
 * Smart Woo REST API sanitization class.
 *
 * These methods should only return safe values, never WP_Error.
 * Validation (rejecting bad values) should be handled in VALIDATE class.
 */
class SANITIZE {

    /**
     * Sanitize an integer value.
     *
     * @param mixed $value The value to sanitize.
     * @return int The sanitized integer.
     */
    public static function integer( $value ) {
        return intval( $value );
    }

    /**
     * Sanitize a string value.
     *
     * @param mixed $value The value to sanitize.
     * @return string The sanitized string.
     */
    public static function string( $value ) {
        return sanitize_text_field( (string) wp_unslash( $value ) );
    }

    /**
     * Sanitize an email address.
     *
     * @param mixed $value The value to sanitize.
     * @return string The sanitized email (may be empty if invalid).
     */
    public static function email( $value ) {
        return sanitize_email( $value );
    }

    /**
     * Sanitize a URL.
     *
     * @param mixed $value The value to sanitize.
     * @return string The sanitized URL (empty string if invalid).
     */
    public static function url( $value ) {
        return esc_url_raw( $value );
    }

    /**
     * Sanitize a boolean value.
     *
     * @param mixed $value The value to sanitize.
     * @return bool The sanitized boolean.
     */
    public static function boolean( $value ) {
        if ( in_array( $value, array( '1', 1, 'true', 'yes', true ), true ) ) {
            return true;
        }
        return false;
    }

    /**
     * Sanitize an array of scalar values.
     *
     * @param mixed $value The value to sanitize.
     * @return array The sanitized array (empty if not array).
     */
    public static function array( $value ) {
        if ( is_array( $value ) ) {
            return array_map( 'sanitize_text_field', wp_unslash( $value ) );
        }
        return array();
    }

    /**
     * Sanitize HTML string
     * 
     * @param $value
     */
    public static function html( $value ) {
        return wp_kses_post( $value );
    }

	/**
	 * Sanitize HTML content based on allowed tags and attributes.
	 *
	 * @param string $html Raw HTML from the editor.
	 * @return string Sanitized HTML.
	 */
	public static function sanitize_editor_html( $html ) {
		$allowed = array(
			'a'       => array( 'href', 'target', 'title', 'rel', 'class', 'style', 'data-*', 'aria-*', 'download' ),
			'abbr'    => array( 'title', 'class', 'style', 'data-*', 'aria-*' ),
			'acronym' => array( 'title', 'class', 'style', 'data-*', 'aria-*' ),
			'b'       => array( 'class', 'style', 'data-*', 'aria-*' ),
			'blockquote' => array( 'cite', 'class', 'style', 'data-*', 'aria-*' ),
			'br'      => array( 'class', 'style', 'data-*', 'aria-*' ),
			'code'    => array( 'class', 'style', 'data-*', 'aria-*' ),
			'div'     => array( 'id', 'class', 'style', 'title', 'data-*', 'aria-*', 'draggable', 'contenteditable' ),
			'em'      => array( 'class', 'style', 'data-*', 'aria-*' ),
			'h1'      => array( 'class', 'style', 'data-*', 'aria-*' ),
			'h2'      => array( 'class', 'style', 'data-*', 'aria-*' ),
			'h3'      => array( 'class', 'style', 'data-*', 'aria-*', 'contenteditable' ),
			'h4'      => array( 'class', 'style', 'data-*', 'aria-*' ),
			'h5'      => array( 'class', 'style', 'data-*', 'aria-*' ),
			'h6'      => array( 'class', 'style', 'data-*', 'aria-*' ),
			'hr'      => array( 'class', 'style', 'data-*', 'aria-*' ),
			'i'       => array( 'class', 'style', 'data-*', 'aria-*' ),
			'iframe'  => array( 'src', 'width', 'height', 'frameborder', 'allowfullscreen', 'class', 'style', 'data-*', 'aria-*' ),
			'img'     => array( 'src', 'alt', 'title', 'width', 'height', 'class', 'style', 'data-*', 'aria-*', 'draggable', 'contenteditable' ),
			'li'      => array( 'class', 'style', 'title', 'data-*', 'aria-*', 'contenteditable', 'draggable' ),
			'ol'      => array( 'class', 'style', 'title', 'data-*', 'aria-*', 'contenteditable' ),
			'ul'      => array( 'class', 'style', 'title', 'data-*', 'aria-*', 'contenteditable' ),
			'p'       => array( 'class', 'style', 'title', 'data-*', 'aria-*', 'contenteditable' ),
			'pre'     => array( 'class', 'style', 'title', 'data-*', 'aria-*' ),
			'section' => array( 'class', 'style', 'data-*', 'aria-*', 'contenteditable' ),
			'article' => array( 'class', 'style', 'data-*', 'aria-*', 'contenteditable' ),
			'small'   => array( 'class', 'style', 'data-*', 'aria-*' ),
			'span'    => array( 'class', 'style', 'title', 'data-*', 'aria-*', 'contenteditable' ),
			'strong'  => array( 'class', 'style', 'data-*', 'aria-*' ),
			'sub'     => array( 'class', 'style', 'data-*', 'aria-*' ),
			'sup'     => array( 'class', 'style', 'data-*', 'aria-*' ),
			'table'   => array( 'border', 'cellspacing', 'cellpadding', 'class', 'style', 'data-*', 'aria-*' ),
			'tbody'   => array( 'class', 'style', 'data-*', 'aria-*' ),
			'thead'   => array( 'class', 'style', 'data-*', 'aria-*' ),
			'tfoot'   => array( 'class', 'style', 'data-*', 'aria-*' ),
			'tr'      => array( 'class', 'style', 'data-*', 'aria-*' ),
			'td'      => array( 'colspan', 'rowspan', 'class', 'style', 'data-*', 'aria-*' ),
			'th'      => array( 'colspan', 'rowspan', 'scope', 'class', 'style', 'data-*', 'aria-*' ),
			'time'    => array( 'datetime', 'class', 'style', 'data-*', 'aria-*' ),
			'video'   => array( 'src', 'poster', 'controls', 'autoplay', 'loop', 'muted', 'preload', 'class', 'style', 'data-*', 'aria-*', 'draggable', 'contenteditable' ),
			'audio'   => array( 'src', 'controls', 'autoplay', 'loop', 'muted', 'preload', 'class', 'style', 'data-*', 'aria-*', 'draggable', 'contenteditable' ),

			// SVG support — allow ALL attributes (like TinyMCE: svg[*])
			'svg'     => '*',
			'path'    => '*',
			'g'       => '*',
			'use'     => '*',
		);

		$dom    = new \DOMDocument();
		$prev   = libxml_use_internal_errors( true );
		// ensure proper encoding handling and avoid inserted html/body wrappers
		$dom->loadHTML( '<?xml encoding="utf-8" ?>' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
		libxml_clear_errors();
		libxml_use_internal_errors( $prev );

		$xpath = new \DOMXPath( $dom );

		/** @var \DOMNodeList $nodes */
		$nodes = $xpath->query( '//*' );

		foreach ( $nodes as $node ) {
			$tag = strtolower( $node->nodeName );

			// If tag is not allowed, unwrap it (preserve children) then remove.
			if ( ! isset( $allowed[ $tag ] ) ) {
				if ( $node->parentNode ) {
					while ( $node->firstChild ) {
						$node->parentNode->insertBefore( $node->firstChild, $node );
					}
					$node->parentNode->removeChild( $node );
				}
				continue;
			}

			// If this tag allows all attributes (SVG ' * '), skip attribute filtering.
			if ( '*' === $allowed[ $tag ] ) {
				continue;
			}

			// Only DOMElement has attributes and removeAttribute method.
			if ( ! ( $node instanceof \DOMElement ) ) {
				continue;
			}

			// Iterate attributes safely as DOMAttr instances.
			foreach ( iterator_to_array( $node->attributes ) as $attr ) {
				if ( ! ( $attr instanceof \DOMAttr ) ) {
					continue;
				}

				$name   = strtolower( $attr->name );
				$is_data = ( 0 === strpos( $name, 'data-' ) );
				$is_aria = ( 0 === strpos( $name, 'aria-' ) );

				if ( ! in_array( $name, $allowed[ $tag ], true ) && ! $is_data && ! $is_aria ) {
					// Use the original attribute name to remove (preserve case if any).
					$node->removeAttribute( $attr->name );
				}
			}
		}

		return trim( $dom->saveHTML() );
	}
}
