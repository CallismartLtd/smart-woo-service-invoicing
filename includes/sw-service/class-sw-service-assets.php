<?php
/**
 * File name: class-sw-service-assets.php
 * Description: Class file for SmartWoo_Service_Assets
 * 
 * @author Callistus
 * @package SmartWoo\class
 * @since 2.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class representation assets assigned to a service subscription.
 * 
 * @since 2.0.0
 * @package SmartWooService
 */
class SmartWoo_Service_Assets {
    /**
     * Asset ID
     * 
     * @var int $id
     */
    protected $asset_id;

    /**
     * Service ID associated with the asset
     * 
     * @var string $service_id
     */
    protected $service_id;

    /**
     * Asset Name
     * 
     * @var string $asset_name
     */
    protected $asset_name;

    /**
     * Asset data
     * 
     * @var array $data Associative array of name => value of an asset
     */
    protected $data = array();

    /**
     * Asset key
     * 
     * @var string $key
     */
    protected $key;

    /**
     * Whether asset has an external url.
     * 
     * @since 2.0.1
     * @var string $is_external
     */
    protected $is_external = 'no';

    /**
     * Asset expiry date
     * 
     * @var string $expiry
     */
    protected $expiry;

    /**
     * Access limit
     * 
     * @var int $limit
     */
    protected $limit = -1;

    /**
     * Date of creation.
     * 
     * @var string $created_at
     */
    protected $created_at;

    /**
     * Date updated
     * 
     * @var string $updated_at
     */
    protected $updated_at;

    /**
     * Class constructor.
     * 
     * @param int $id Asset ID.
     */
    public function __construct( $id = 0 ) {
        if ( ! empty( $id ) && ! is_string( $id ) ) {
            $this->get_asset( $id, false );
        }

    }

    /*
    |----------
    | SETTERS
    |----------
    */

    /**
     * Set Asset ID
     * 
     * @param int $id The database id.
     */
    public function set_id( $id ) {
        $this->asset_id = absint( $id );
    }

    /**
     * Set Service ID
     * 
     * @param string $service_id The service id associated.
     */
    public function set_service_id( $service_id ) {
        $this->service_id = sanitize_text_field( $service_id );
    }

    /**
     * Set asset name
     * 
     * @param $name the name of this asset.
     */
    public function set_asset_name( $name ) {
        $this->asset_name = sanitize_text_field( $name );
    }

    /**
     * Set asset data.
     * 
     * @param array|string $data Associative array of name => value of an asset or a serialized string. 
     */
    public function set_asset_data( $data, $context = 'view' ) {

        if ( 'view' === $context ) {
            $this->data = wp_unslash( (array) $data );

        } elseif ( 'db_save' === $context ) {
            $this->data = maybe_serialize( $data );
        } elseif ( 'db_get' === $context ) {
            $this->data = maybe_unserialize( $data );
        }
    }

    /**
     * Set asset key
     * 
     * @param string $key The Access key for this asset.
     */
    public function set_key( $key ) {
        $this->key = sanitize_text_field( $key );
    }

    /**
     * Set expiry
     * 
     * @param string $date The expiry date.
     */
    public function set_expiry( $date ) {
        $this->expiry = sanitize_text_field( $date );
    }

    /**
     * Set Access limit.
     * 
     * @param int $limit The limit before the asset will be in-accessible
     */
    public function set_limit( $limit ) {
        $this->limit = intval( $limit );
    }

    /**
     * Set Date created
     * 
     * @param string $date The date an asset is created.
     */
    public function set_created_at( $date ) {
        $this->created_at = sanitize_text_field( $date );
    }

    /**
     * Set Date Updated
     * 
     * @param string $date The date an asset was last updated.
     */
    public function set_updated_at( $date ) {
        $this->updated_at = sanitize_text_field( $date );
    }

    /*
    |-------------
    | GETTERS
    |-------------
    */

    /**
     * Get Asset ID
     */
    public function get_id() {
        return $this->asset_id;
    }

    /**
     * Get Service ID
     */
    public function get_service_id() {
        return $this->service_id;
    }

    /**
     * Get Asset Name
     */
    public function get_asset_name() {
        return $this->asset_name;
    }

    /**
     * Get Asset Data.
     */
    public function get_asset_data( $context = 'view' ) {
        if ( 'db_save' === $context ) {
            $data   = is_array( $this->data ) ? maybe_serialize( $this->data ) : $this->data;
            return $data;
        }
        return is_serialized( $this->data ) ? maybe_unserialize( $this->data ) : $this->data;
    }

    /**
     * Get a specific access data
     * 
     * @param int|string $key
     */
    public function get_data( $key ) {
        if ( is_array( $this->data ) && array_key_exists( $key, $this->data ) ) {
            return $this->data[$key];
        }
        return null;
    }

    /**
     * Get Asset Key
     */
    public function get_key() {
        return $this->key;
    }

    /**
     * Get access limit
     */
    public function get_access_limit( $context = 'view') {
        
        if ( 'view' === $context && intval( $this->limit ) < 0 ) {
            return 'Unlimited';
        } elseif (  'view' === $context && intval( $this->limit ) === 0 ) {
            return 'Exceeded';
        } elseif ( 'edit' === $context && $this->limit <= -1 ) {
            $this->limit = '';
        }
        return $this->limit;
    }

    /**
     * Get asset expiry
     */
    public function get_expiry() {
        return $this->expiry;
    }

    /**
     * Get date created
     */
    public function get_created_at() {
        return $this->created_at;
    }

    /**
     * Get date updated
     */
    public function get_updated_at() {
        return $this->updated_at;
    }

    /*
    |----------------
    | CRUD METHODS
    |----------------
    */

    /**
     * Get an asset by id
     * 
     * @param int $id
     */
    public function get_asset( $id, $new_self = true ) {
        if ( empty( $id ) ) {
            return false;
        }
        global $wpdb;
        $id         = absint( $id );
        $table_name = SMARTWOO_ASSETS_TABLE;
        $query      = $wpdb->prepare( "SELECT * FROM {$table_name} WHERE `asset_id` = %d", $id ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $result     = $wpdb->get_row( $query, ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
    
        if ( ! empty( $result ) ) {
            return $this->convert_db_result( $result, $new_self  );
        }

        return false;
    }

    /**
     * Get assets associated with a service subscription.
     */
    public function get_service_assets() {
        if ( empty( $this->service_id ) ) {
            return null;
        }

        global $wpdb;
        $query  = $wpdb->prepare( "SELECT * FROM ". SMARTWOO_ASSETS_TABLE . " WHERE `service_id` =%s", $this->service_id ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --false positive
        $results = $wpdb->get_results( $query, ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

        $assets = array();
        if ( ! empty( $results ) ) {
            foreach ( $results as $result ){ 
                $assets[] = $this->convert_db_result( $result );                
            }

            return $assets;
        }

        return null;
    }

    /**
     * Get an asset by its name
     */
    public function get_by_asset_name() {
        if ( empty( $this->asset_name ) || empty( $this->service_id ) ) {
            return null;
        }

        global $wpdb;
        $query  = $wpdb->prepare( "SELECT * FROM ". SMARTWOO_ASSETS_TABLE . " WHERE `service_id` =%s AND `asset_name` =%s", $this->service_id, $this->asset_name ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $result = $wpdb->get_row( $query, ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

        if ( $result ) {
            return $this->convert_db_result( $result );
        }

        return null;
    }

    /**
     * Get asset data only
     * 
     * @param int $asset_id The asset ID to look.
     * @param string $key Asset key.
     * @return array Asset data if successful, empty array otherwise.
     */
    public static function return_data( $asset_id, $key, &$this_obj = null ) {
        global $wpdb;
        $query  = $wpdb->prepare(  
            "SELECT `asset_data`, `access_limit` FROM " . SMARTWOO_ASSETS_TABLE . " WHERE `asset_id` = %d AND `asset_key` = %s", // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
            absint( $asset_id ), sanitize_text_field( $key ) 
        );
        $result = $wpdb->get_row( $query, ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
        
        if ( ! $result ) {
            return array();
        }

        $this_obj  = new self( absint( $asset_id ) );
        return wp_unslash( maybe_unserialize( $result['asset_data'] ) );
        
    }

    /**
     * Save asset.
     */
    public function save() {
        if ( empty( $this->service_id ) ) {
            return false;
        }

        global $wpdb;

        $is_update  = false;
        $is_new     = false;

        if ( ! empty( $this->asset_id ) ) {
            $is_update = true;
        } else {
            $is_new = true;
        }

        $data = array(
            'service_id'    => $this->service_id,
            'asset_name'    => $this->asset_name,
            'asset_data'    => $this->get_asset_data( 'db_save' ),
            'asset_key'     => empty( $this->key ) ? 'sw_' . smartwoo_generate_token() : $this->key,
            'is_external'   => $this->is_external,
            'access_limit'  => $this->limit,
            'expiry'        => $this->expiry,
        );

        $data_format    = array( '%s', '%s', '%s', '%s', '%s', '%s' );

        if ( $is_update ) {
            $data['updated_at'] = current_time( 'mysql' );
            $data_format = array_merge( $data_format, array( '%s' ) );
            $updated = $wpdb->update(  // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                SMARTWOO_ASSETS_TABLE,
                $data, 
                array( 'asset_id' => $this->asset_id ),
                $data_format,
                array( '%d' ),
            );

            return $updated !== false;

        }

        if ( $is_new ) {
            $data['created_at'] = current_time( 'mysql' );
            $data_format = array_merge( $data_format, array( '%s' ) );

            $inserted = $wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                SMARTWOO_ASSETS_TABLE,
                $data,
                $data_format
            );

            if ( $inserted ) {
                $this->set_id( $wpdb->insert_id );
            }
            return $inserted !== false;
        }

        return false;
    }

    /**
     * Delete a single asset record
     */
    public function delete() {
        if ( empty( $this->asset_id ) ) {
            return false;
        }

        global $wpdb;
        
        $deleted = $wpdb->delete( SMARTWOO_ASSETS_TABLE, array( 'asset_id' => $this->asset_id ), array( '%d' ) );  // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        return $deleted !== false;
    }

    /**
     * Deletes every Asset related this the service id in the database.
     */
    public function delete_all() {
        if ( empty( $this->service_id ) || ! $this->exists( $this->service_id ) ) {
            return false;
        }

        global $wpdb;
        
        $deleted = $wpdb->delete( SMARTWOO_ASSETS_TABLE, array( 'service_id' => $this->service_id ), array( '%s' ) );  // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        return $deleted !== false;
    }

    /*
    |-----------------
    | UTILITY METHODS
    |-----------------
    */

    /**
     * Check or set external property
     * 
     * @param mixed $value The value
     */
    public function is_external( $value = '' ) {
        if ( empty( $value ) ) {
            return $this->is_external;
        }

        $this->is_external = wc_bool_to_string( wc_string_to_bool( $value ) ); 
       
        return $this->is_external;
        
    }

    /**
     * Check if a service id exists in the database.
     * 
     * @param string $service_id TAhe service ID.
     */
    public function exists( $service_id ) {
        global $wpdb;
		$query 	= $wpdb->prepare( "SELECT `service_id` FROM " . SMARTWOO_ASSETS_TABLE . " WHERE `service_id` = %s", $this->service_id ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$result	= $wpdb->get_var( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		return $result !== null;
    }

    /**
     * Convert database result to an object of this class.
     * 
     * @param array $result A database query result of an associative array.
     */
    public function convert_db_result( $result, $new_self = true ) {
        $self = $new_self ? new self() : $this;
        $self->set_id( $result['asset_id'] );
        $self->set_service_id( $result['service_id'] );
        $self->set_asset_name( $result['asset_name'] );
        $self->set_asset_data( $result['asset_data'], 'db_get' );
        $self->set_key( $result['asset_key'] );
        $self->is_external( $result['is_external'] );
        $self->set_limit( $result['access_limit'] );
        $self->set_expiry( $result['expiry'] );
        $self->set_created_at( $result['created_at'] );
        $self->set_updated_at( $result['updated_at'] );
        return $self;
    }

    /**
     * Convert array to an object of this class.
     * 
     * @param array $array Associative array.
     */
    public static function convert_arrays( $result, $context = 'view' ) {
        $self = new self();
        $self->set_id( ! empty( $result['asset_id'] ) ? $result['asset_id'] : 0 );
        $self->set_service_id( ! empty( $result['service_id'] ) ? $result['service_id'] : '' );
        $self->set_asset_name( ! empty( $result['asset_name'] ) ? $result['asset_name'] : '' );
        $self->set_asset_data( ! empty( $result['asset_data'] ) ? $result['asset_data'] : '', $context );
        $self->set_key( ! empty( $result['asset_key'] ) ? $result['asset_key'] : '' );
        $self->set_expiry( ! empty( $result['expiry'] ) ? $result['expiry'] : '' );
        $self->set_limit( ! empty( $result['access_limit'] ) ? $result['access_limit'] : '' );
        $self->is_external( ! empty( $result['is_external'] ) ? $result['is_external']: '' );

        return $self;
    }

    /**
     * Verify Access key.
     * 
     * @param string $key The access key.
     * @param int $data_index The position of the value of the asset data.
     */
    public static function verify_key( $key, $data_index ) {
        $key        = ! empty( $key ) && is_string( $key ) ? sanitize_text_field( $key ) : false;
        $data_index = ! empty( $data_index ) && is_numeric( $data_index ) ? absint( $data_index ) : false;
        
        if ( ! $key || ! $data_index ) {
            return false;
        }

        global $wpdb;

        $query = $wpdb->prepare( "SELECT `asset_data`, `access_limit` FROM " . SMARTWOO_ASSETS_TABLE . " WHERE `asset_key` =%s", $key ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $result = $wpdb->get_row( $query, ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

        if ( ! $result ) {
            return false;
        }

        // $limit  = $result['access_limit'];

        // if ( 0 === intval( $limit ) ) {
        //     return false;
        // } elseif ( $limit > 0 ) {
        //     $wpdb->update( SMARTWOO_ASSETS_TABLE, // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        //         array( 'access_limit' => intval( $limit ) - 1 ),
        //         array( 'asset_key' => $key ),
        //         array( '%d' ),
        //         array( '%s' ),
        //      );

        // }


        $asset_data = array_values( (array) maybe_unserialize( $result['asset_data'] ) );

        return array_key_exists( $data_index - 1, $asset_data );
    }

    /**
     * Ajax callback to delete an asset.
     * 
     * @since 2.0.1
     */
    public static function ajax_delete() {
        if ( ! check_ajax_referer( sanitize_text_field( wp_unslash( 'smart_woo_nonce' ) ), 'security', false ) ) {
            wp_send_json_error( array( 'message' => 'Action failed basic authentication.' ) );
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'You don\'t have the required permission to delete this data.' ) );
        }

        $asset_id = isset( $_GET['asset_id'] ) ? absint( $_GET['asset_id'] ) : 0;

        if ( empty( $asset_id ) ) {
            wp_send_json_error( array( 'message' => 'Asset ID not provided.' ) );
        }

        $self = new self();
        $self->set_id( $asset_id );
        
        if ( $self->delete() ) {
            wp_send_json_success( array( 'message' => 'Asset has been removed' ) );
        }
        
        wp_send_json_error( array( 'message' => 'An error occured when deleting asset.' ) );
    }
}