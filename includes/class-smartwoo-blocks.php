<?php
/**
 * SmartWoo Blocks Class file.
 * 
 * @author Callistus Nwachukwu
 * @package SmartWoo\Blocks
 * @since 2.5.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * SmartWoo Blocks Class handles all block-related functionalities.
 */

class SmartWoo_Blocks {
    /**
     * Singleton instance of current class.
     */
    private static $instance = null;

    /**
     * All Smart Woo Blocks.
     * 
     * @var array $blocks
     */
    protected $blocks = array();

    /**
     * Class constructor.
     */
    private function __construct() {
        $this->load_blocks();
        $this->register_blocks();
        add_action( 'after_setup_theme', array( $this, 'after_setup_theme' ) );
    }

    /**
     * Load all Smart Woo Blocks.
     * 
     * @filter smartwoo_blocks{
     * 
     * @param array $block_directories.
     * 
     * }
     * 
     * @return void.
     */
    private function load_blocks() {
        $block_dir      = SMARTWOO_PATH . 'templates/blocks/build/';
        $block_folders  = glob( $block_dir . '*', GLOB_ONLYDIR );
        $this->blocks   = apply_filters( 'smartwoo_blocks', $block_folders );
    }

    /**
     * Register all blocks.
     * 
     * @return void
     */
    private function register_blocks() {
        foreach ( $this->blocks as $block ) {
            if ( file_exists( trailingslashit( $block ) . 'block.json' ) ) {
                register_block_type( $block );
            } else {
                error_log( "SmartWoo: Missing block.json in $block" );
            }
        }
    }

    /**
     * Run after setup theme actions and filters.
     */
    public function after_setup_theme() {
        add_theme_support( 'appearance-tools' );
        add_theme_support( 'border' );
        add_theme_support( 'wp-block-styles' );
    }

    /**
     * Register blocks category.
     * 
     * @param array $categories
     * @param WP_Block_Editor_Context $context
     */
    public static function register_block_category( $categories, $context ) {
        if ( ! is_array( $categories ) ) {
            return $categories;
        }
        

        $categories[] = array(
            'slug'  => 'smart-woo-blocks',
            'title' => __( 'Smart Woo Blocks', 'smart-woo-service-invoicing' ),
        );

        return $categories;
    }

    /**
     * Instanciate a singleton instance of this class.
     */
    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}