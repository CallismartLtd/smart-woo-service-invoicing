<?php
/**
 * File name    :   sw-db-update.php
 *
 * @author      :   Callistus
 * @package SmartWoo\Database
 * Description  :   Database migration or update file
 */

defined( 'ABSPATH' ) || exit; // Prevent direct access

/**
 * SmartWoo_DB_Update class.
 */
class SmartWoo_DB_Update extends SmartWoo_Install {
    /**
     * Database updates and functions that need to be run per version.
     * These callback functions are called when Smart Woo updates from a previous version and not
     * when it's newly installed. All changes to database schema should be implemented in the @see smartwoo_db_schema().
     * 
     * @var array $schema_changes Associative array of DB version => callback_function
     */
    protected $schema_changes = array(
        '2.0.1' => array(
            'smartwoo_db_update_201_is_external',
            'smartwoo_migrate_options_201',
        ),

        '2.0.12' => array(
            'smartwoo_2012_update_service_count'
        ),

        '2.2.0' => array(
            'smartwoo_220_mail_option_update'
        )
    );

    /**
     * Saved DB version
     */
    protected $db_version = '';

    /**
     * Status of current update
     */
    protected $is_updating = false;

    /**
     * Instance of SmartWoo_DB_Update
     * 
     * @var SmartWoo_DB_Update
     */
    private static $instance = null;

    /**
     * Class constructor
     */
    public function __construct() {

        $this->is_updating = 'running' === get_transient( 'smartwoo_db_update' );
        $this->db_version       = SMARTWOO_DB_VER;

        if ( parent::is_new_installation() || parent::is_installing() ) {
            return;
        }

        add_action( 'admin_notices', array( $this, 'add_update_notice' ) );
        add_action( 'admin_notices', array( $this, 'update_in_progress_notice' ) );
        add_action( 'wp_ajax_smartwoo_db_update', array( $this, 'run_db_update' ) );
    }

    /**
     * Add database update notice
     */
    public function add_update_notice() {
        if ( $this->is_updating ) {
            return;
        }

        $stored_version = get_option( 'smartwoo_db_version', 0 );
        if ( $this->db_version !== $stored_version ) {
            $notice = '<div id="smartwooNoticeDiv" class="notice notice-warning"><p>' . smartwoo_notice( 'Smart Woo database requires an update' ) . '</p>';
            $notice .= '<p>Smart Woo Service Invoicing need to update its database <a class="button" id="smartwooUpdateBtn">Run Backgroud Update</a></p>';
            $notice .= '</div>';

            echo wp_kses_post( $notice );
        }
    }

    /**
     * Add update in progress notice
     */
    public function update_in_progress_notice() {
        $echo = false;
        $stored_version = get_option( 'smartwoo_db_version', 0 );
        if ( $this->db_version === $stored_version && $this->is_updating ) {
            $notice = '<div class="notice notice-info is-dismissible"><p>Smart Woo Database update is completed</p></div>';
            $echo = true;
            delete_transient( 'smartwoo_db_update' );
        } elseif ( $this->is_updating ) {
            $notice = '<div class="notice notice-info"><p>Smart Database update in progress</p></div>';
            $echo = true;
        }
        
        if ( $echo ) {
            echo wp_kses_post( $notice );
        }
        
    }

    /**
     * Single instance of current SmartWoo_DB_Update
     */
    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
    }

    /**
     * Run database update
     */
    public function run_db_update() {
        if ( ! check_ajax_referer( 'smart_woo_nonce', 'security', false ) ) {
            wp_send_json_error( array( 'message' => 'Action failed basic authentication' ), 401 );
        }

        set_transient( 'smartwoo_db_update', 'running', 90 );
        // Create table that do not exist.
        parent::create_tables();
        // Run column update since dbDelta() might be unreliable for this.
        $current_dbver_changed   = array_key_exists( $this->db_version, $this->schema_changes ) ? $this->schema_changes[$this->db_version] : false;
        if ( $current_dbver_changed ) {
            // Load the db table file to have access to the properties.
		    include_once SMARTWOO_PATH . 'includes/admin/include/sw-db.php';
            foreach( $current_dbver_changed as $func_to_call ) {
                if ( function_exists( $func_to_call ) && is_callable( $func_to_call ) ) {
                    call_user_func( $func_to_call );
                }
                
            }
        }
        wp_send_json_success( array( 'message' => 'Database update is running in the background.' ) );
    }
}

SmartWoo_DB_Update::instance();