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
namespace Callismart;
use \WP_Error;

defined( 'ABSPATH' ) || exit;

/**
 * SmartWoo Inbox Manager Class.
 *
 * @since 1.0.0
 */
class SupportInbox {

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

		return $this->save();
	}

    /**
     * Mark a message as read.
     *
     * @param string $message_id Message unique ID.
     * @return bool|WP_Error
     */
    public function mark_as_read( $message_id ) {

        if ( ! $this->has_message( $message_id ) ) {
            return new WP_Error( 'message_not_found', __( 'Message not found.', 'smart-woo-service-invoicing' ), array( 'status' => 404 ) );
        }

        $message			= $this->get_message( $message_id );
		$message['read']	= true;

        return $this->save_message( $message );
    }

    /**
     * Mark all messages as read.
     *
     * @since 0.0.6
     * @return bool|WP_Error
     */
    public function mark_all_as_read() {
        $messages = $this->get_messages();

        if ( empty( $messages ) ) {
            return new WP_Error( 'no_messages', __( 'No messages found in inbox.', 'smart-woo-service-invoicing' ) );
        }

		$marked	= 0;

        foreach ( $messages as $id => &$message ) {
			$result	= $this->mark_as_read( $id );
            if ( true === $result ) {
				$marked++;
			}
        }

        return $marked;
    }

    /**
     * Mark a message as unread.
     *
     * @param string $message_id Message unique ID.
     * @return bool|WP_Error
     */
    public function mark_as_unread( $message_id ) {

        if ( ! $this->has_message( $message_id ) ) {
            return new WP_Error( 'message_not_found', __( 'Message not found.', 'smart-woo-service-invoicing' ), array( 'status' => 404 ) );
        }

        $message			= $this->get_message( $message_id );
		$message['read']	= false;

        return $this->save_message( $message );
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
	 * Get a particular message using the id
	 * 
	 * @param string $message_id The message ID.
	 * @return array $message
	 */
	public function get_message( $message_id ) {
		$messages = $this->get_messages();
		return $messages[$message_id] ?? array();
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
	 * @return bool
	 */
	public function delete_message( $message_id ) {
		if ( isset( $this->data['messages'][ $message_id ] ) ) {
			unset( $this->data['messages'][ $message_id ] );
			return $this->save();
		}

		return false;
	}

	/**
	 * Save inbox data back to the options table.
	 *
	 * @return bool
	 */
	protected function save() {
		return update_option( self::$option_key, wp_json_encode( $this->data ), false );
	}

	/*--------------------------------------------------------------
	# SYNCING / FETCHING
	--------------------------------------------------------------*/
    /**
     * Fetch new messages from a remote API if eligible.
     *
     * Used by CRON jobs or manual user actions. The $force parameter allows
     * bypassing the daily rate limit.
     *
     * @since 1.0.0
     *
     * @param bool $force Optional. Whether to force a new fetch even if last check
     *                    was recent. Default false.
     * @return true|WP_Error True if messages were updated, or WP_Error on failure.
     */
    public function maybe_fetch_messages( $force = false ) {
        // Respect consent.
        if ( ! $this->has_consent() ) {
            return new WP_Error(
                'smartwoo_no_consent',
                __( 'User has not given consent to receive messages.', 'smart-woo-service-invoicing' )
            );
        }

        // Prevent frequent checks unless forced.
        if ( ! $force && $this->data['last_checked'] && ( time() - strtotime( $this->data['last_checked'] ) ) < DAY_IN_SECONDS ) {
            return new WP_Error(
                'smartwoo_recently_checked',
                __( 'Messages were recently checked. Try again later or use force refresh.', 'smart-woo-service-invoicing' )
            );
        }

        $endpoint = 'https://apiv1.callismart.local/wp-json/smliser/v1/mock-inbox';

        $response = wp_remote_get(
            esc_url_raw( $endpoint ),
            array(
                'timeout'   => 30,
                'sslverify' => false, // Set to true in production.
            )
        );

        if ( is_wp_error( $response ) ) {
            return new WP_Error(
                'http_request_failed',
                sprintf(
                    /* translators: %s: error message */
                    __( 'Failed to fetch messages: %s', 'smart-woo-service-invoicing' ),
                    $response->get_error_message()
                )
            );
        }

        $body = wp_remote_retrieve_body( $response );

        if ( empty( $body ) ) {
            return new WP_Error(
                'empty_response',
                __( 'Empty response from the remote inbox API.', 'smart-woo-service-invoicing' )
            );
        }

        $messages = json_decode( $body, true );

        if ( json_last_error() !== JSON_ERROR_NONE || ! is_array( $messages ) ) {
            return new WP_Error(
                'invalid_json',
                __( 'Invalid JSON data received from the remote server.', 'smart-woo-service-invoicing' )
            );
        }

        // Process and store messages.
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
	 * Check whether a message with an ID exists
	 * 
	 * @param string $message_id
	 * @return bool
	 */
	public function has_message( $message_id ) {
		return isset( $this->data['messages'][ $message_id ] );
	}

}
