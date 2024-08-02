<?php
/**
 * File name: class-sw-service-assets.php
 * Description: Class file for SmartWoo_Service_Assets
 * 
 * @author Callistus
 * @package SmartWoo\class
 * @since 2.0.0
 */

define( 'ABSPATH' ) || exit;

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
    protected $limit;

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
     * Class constructor
     */
    public function __construct() {}

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
        $this->limit = absint( $limit );
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
    public function get_access_limit() {
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
     * Get assets associated with a service subscription.
     */
    public function get_service_assets() {
        if ( empty( $this->service_id ) ) {
            return null;
        }

        global $wpdb;
        $query  = $wpdb->prepare( "SELECT * FROM ". SMARTWOO_ASSETS_TABLE . " WHERE `service_id` =%s", $this->service_id );
        $results = $wpdb->get_results( $query, ARRAY_A );

        $assets = array();
        if ( ! empty( $results ) ) {
            foreach ( $results as $result ){ 
                $assets[] = $this->convert_db_result( $results );                
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
        $query  = $wpdb->prepare( "SELECT * FROM ". SMARTWOO_ASSETS_TABLE . " WHERE `service_id` =%s AND `asset_name` =%s", $this->service_id, $this->asset_name );
        $result = $wpdb->get_row( $query, ARRAY_A );

        if ( $result ) {
            return $this->convert_db_result( $result );
        }

        return null;
    }

    /**
     * Save asset.
     */
    public function save() {
        if ( empty( $this->service_id ) ) {
            return false;
        }

        global $wpdab;

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
            'asset_key'     => $this->key,
            'access_limit'  => $this->limit,
            'expiry'        => $this->expiry,
        );

        $data_format    = array( '%s', '%s', '%s', '%s', '%s', '%s' );

        if ( $is_update ) {
            $data['updated_at'] = current_time( 'mysql' );
            $data_format = array_merge( $data_format, array( '%s' ) );
            $updated = $wpdb->update( 
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

            $inserted = $wpdb->insert(
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






    /*
    |-----------------
    | UTILITY METHODS
    |-----------------
    */

    /**
     * Convert database result to an object of this class.
     * 
     * @param array $result A database query result of an associative array.
     */
    public function convert_db_result( $result ) {
        $this->set_id( $result['asset_id'] );
        $this->set_service_id( $result['service_id'] );
        $this->set_asset_name( $result['asset_name'] );
        $this->set_asset_data( $result['asset_data'], 'db_get' );
        $this->set_key( $result['key'] );
        $this->set_limit( $result['access_limit'] );
        $this->set_expiry( $result['expiry'] );
        $this->set_created_at( $result['created_at'] );
        $this->set_updated_at( $result['updated_at'] );
        return $this;
    }
}