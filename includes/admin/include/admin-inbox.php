<?php
/**
 * Handles inbox messages and user communication within Smart Woo plugins.
 *
 * This class manages local storage of inbox messages, user consent,
 * and message synchronization with a remote server. Messages are stored
 * in the wp_options table as a JSON-encoded array.
 *
 * @package SmartWoo
 * @subpackage Core
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * SmartWoo Inbox Manager Class.
 *
 * @since 1.0.0
 */
class SmartWoo_Inbox_Manager {

	/**
	 * Option key for storing inbox data.
	 *
	 * @var string
	 */
	protected static $option_key = 'smartwoo_inbox_data';

	/**
	 * Cached inbox data.
	 *
	 * @var array
	 */
	protected $data = array();

	/**
	 * Initialize inbox data from the options table.
	 */
	public function __construct() {
		$this->data = json_decode( get_option( self::$option_key, '{}' ), true );

		if ( ! is_array( $this->data ) ) {
			$this->data = array();
		}

		$this->data = wp_parse_args(
			$this->data,
			array(
				'consent'      => false,
				'last_checked' => '',
				'messages'     => array(),
			)
		);
	}

	/*--------------------------------------------------------------
	# CONSENT MANAGEMENT
	--------------------------------------------------------------*/

	/**
	 * Check whether user has opted in to receive inbox messages.
	 *
	 * @return bool
	 */
	public function has_consent() {
		return (bool) $this->data['consent'];
	}

	/**
	 * Update user consent for receiving messages.
	 *
	 * @param bool $consent Whether the user has consented.
	 * @return void
	 */
	public function set_consent( $consent ) {
		$this->data['consent'] = (bool) $consent;
		$this->save();
	}

	/*--------------------------------------------------------------
	# MESSAGE HANDLING
	--------------------------------------------------------------*/

	/**
	 * Add or update an inbox message.
	 *
	 * @param array $message {
	 *     Message properties.
	 *
	 *     @type string $id          Unique message ID.
	 *     @type string $subject     Message subject/title.
	 *     @type string $body        Message body text (HTML supported).
	 *     @type string $created_at  Message creation date.
	 *     @type string $updated_at  Message last update date.
	 *     @type bool   $read        Whether message has been read.
	 * }
	 * @return void
	 */
	public function save_message( $message ) {
		if ( empty( $message['id'] ) ) {
			return;
		}

		$message_id = sanitize_text_field( $message['id'] );

		$this->data['messages'][ $message_id ] = wp_parse_args(
			$message,
			array(
				'id'         => $message_id,
				'subject'    => '',
				'body'       => '',
				'created_at' => current_time( 'mysql' ),
				'updated_at' => current_time( 'mysql' ),
				'read'       => false,
			)
		);

		$this->save();
	}

	/**
	 * Mark a message as read.
	 *
	 * @param string $message_id Message ID.
	 * @return void
	 */
	public function mark_as_read( $message_id ) {
		if ( isset( $this->data['messages'][ $message_id ] ) ) {
			$this->data['messages'][ $message_id ]['read'] = true;
			$this->save();
		}
	}

	/**
	 * Get all stored inbox messages.
	 *
	 * @return array
	 */
	public function get_messages() {
		return (array) $this->data['messages'];
	}

	/**
	 * Get unread messages only.
	 *
	 * @return array
	 */
	public function get_unread_messages() {
		return array_filter(
			$this->data['messages'],
			function( $message ) {
				return empty( $message['read'] );
			}
		);
	}

	/**
	 * Delete a specific message.
	 *
	 * @param string $message_id Message ID.
	 * @return void
	 */
	public function delete_message( $message_id ) {
		if ( isset( $this->data['messages'][ $message_id ] ) ) {
			unset( $this->data['messages'][ $message_id ] );
			$this->save();
		}
	}

	/*--------------------------------------------------------------
	# SYNCING / FETCHING
	--------------------------------------------------------------*/

	/**
	 * Fetch new messages from a remote API if eligible.
	 *
	 * @param string $endpoint Remote URL to fetch messages from.
	 * @return bool True if messages were updated.
	 */
	public function maybe_fetch_messages( $endpoint ) {
		// Respect consent.
		if ( ! $this->has_consent() ) {
			return false;
		}

		// Limit to once daily.
		if ( $this->data['last_checked'] && ( time() - strtotime( $this->data['last_checked'] ) ) < DAY_IN_SECONDS ) {
			return false;
		}

		$response = wp_remote_get( esc_url_raw( $endpoint ) );

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$body = wp_remote_retrieve_body( $response );
		$messages = json_decode( $body, true );

		if ( ! is_array( $messages ) ) {
			return false;
		}

		foreach ( $messages as $message ) {
			if ( isset( $message['id'] ) ) {
				$this->save_message( $message );
			}
		}

		$this->data['last_checked'] = current_time( 'mysql' );
		$this->save();

		return true;
	}

	/*--------------------------------------------------------------
	# UTILITIES
	--------------------------------------------------------------*/

	/**
	 * Save inbox data back to the options table.
	 *
	 * @return void
	 */
	protected function save() {
		update_option( self::$option_key, wp_json_encode( $this->data ), false );
	}
}
