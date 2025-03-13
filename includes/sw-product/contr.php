<?php
/**
 * Smart Woo Product admin page controller class file.
 *
 * @author Callistus
 * @package SmartWoo\admin\templates
 */

defined( 'ABSPATH' ) || exit; // Prevent direct access.

/**
 * SmartWoo Product Controller class.
 */
class SmartWoo_Product_Controller{
    /**
     * @var self $instance
     */
    private static $instance = null;

    /**
     * Expected product form fields.
     * 
     * @var array
     */
    private $form_fields = array(
        'smartwoo_product_id'   => 0,
        'product_name'          => '',
        'product_slug'          => '',
        'description'           => '',
        'regular_price'         => '',
        'sign_up_fee'           => '',
        'sale_price'            => '',
        'date_on_sale_from'  => '',
        'date_on_sale_to'    => '',
        'upsell_ids'           => array(),
        'cross_sell_ids'       => array(),
        'short_description'     => '',
        'product_status'        => '',
        'visibility'            => '',
        '_is_featured'          => false,
        'is_sold_individually'  => false,
        'grace_period_number'   => 0,
        'grace_period_unit'     => '',
        'billing_cycle'         => '',
        'product_image_id'      => '',
        'product_gallery_ids'   => array(),
        'product_category_ids'  => array(),
        'is_smartwoo_downloadable'      => false,
        'sw_downloadable_file_names'    => array(),
        'sw_downloadable_file_urls'     => array(),
        
    );

    /**
     * Instanciate a singleton instance of this class.
     * 
     * @return self
     */
    public static function instance() {
        if ( is_null( self::$instance )) {
            self::$instance = new self();
        }

        return self::$instance;
    }
    /**
     * Action hooks runner.
     */
    public static function listen() {
        
        add_action( 'wp_ajax_smartwoo_create_product',array( __CLASS__, 'product_form_submit' ) );
        add_action( 'wp_ajax_smartwoo_edit_product', array( __CLASS__, 'product_form_submit' ) );
        
        add_action( 'admin_post_smartwoo_create_product', array( __CLASS__, 'product_form_submit' ) );
        add_action( 'admin_post_smartwoo_edit_product', array( __CLASS__, 'product_form_submit' ) );
    }

    /**
     * The submenu page controller.
     */
    public static function menu_controller() {
        $tab    = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    
        // Handle different tabs.
        switch ( $tab ) {
            case 'add-new':
                self::add_page();
                break;
            case 'edit':
                self::edit_page();
                break;
            case 'sort-by':
                self::sort_by();
                break;
            default:
                self::dashboard();
                break;
        }
    }

    /**
     * The Product admin dashboard.
     */
    private static function dashboard() {
        $tab            = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $paged          = isset( $_GET[ 'paged' ] ) ? absint( $_GET[ 'paged' ] ) : 1;
        $limit          = isset( $_GET['limit'] ) ? absint( $_GET['limit'] ) : 25;
        $all_prod_count = SmartWoo_Product::count_all();
        $total			= ceil( $all_prod_count / $limit );
        $next			= $paged + 1;
        $prev			= $paged - 1;

        $products    	= SmartWoo_Product::get_all( array( 'page' => $paged, 'limit' => $limit ) );
        $tabs           = array(
            ''        => 'Products',
            'add-new' => 'Add New',
    
        );

        $status_counts  = array(
            'publish'   => SmartWoo_Product::count_all( 'publish' ),
            'private'   => SmartWoo_Product::count_all( 'private' ),
            'draft'     => SmartWoo_Product::count_all( 'draft' ),
            'pending'   => SmartWoo_Product::count_all( 'pending' ),
            'trash'     => SmartWoo_Product::count_all( 'trash' )
        );

        $status = ''; // For compatibility with the sort_by method.
        $not_found_text = __( 'When you create a new service product, it will appear here.', 'smart-woo-service-invoicing' );
    
        include_once SMARTWOO_PATH . 'templates/product-admin-temp/dashboard.php';
    }

    /**
     * Sort products by status/visibility.
     */
    private static function sort_by() {
        $tab            = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $status         = isset( $_GET['status'] ) ? sanitize_key( $_GET['status'] ) : 'publish';
        $paged          = isset( $_GET[ 'paged' ] ) ? absint( $_GET[ 'paged' ] ) : 1;
        $limit          = isset( $_GET['limit'] ) ? absint( $_GET['limit'] ) : 25;
        $all_prod_count = SmartWoo_Product::count_all( $status );
        $total			= ceil( $all_prod_count / $limit );
        $next			= $paged + 1;
        $prev			= $paged - 1;

        $products    	= SmartWoo_Product::get_all( array( 'page' => $paged, 'limit' => $limit, 'status' => $status ) );
        $tabs           = array(
            ''        => 'Products',
            'add-new' => 'Add New',
    
        );

        $status_counts  = array(
            'publish'   => SmartWoo_Product::count_all( 'publish' ),
            'private'   => SmartWoo_Product::count_all( 'private' ),
            'draft'     => SmartWoo_Product::count_all( 'draft' ),
            'pending'   => SmartWoo_Product::count_all( 'pending' ),
            'trash'     => SmartWoo_Product::count_all( 'trash' )
        );

        $not_found_text = 'No "' . ucfirst( $status ) . '" product found.';
    
        include_once SMARTWOO_PATH . 'templates/product-admin-temp/dashboard.php';
    }

    /**
     * Add new product page
     */
    private static function add_page() {
        $tab            = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $tabs           = array(
            ''        => 'Dashboard',
            'add-new' => 'Add New',
    
        );
        $product_categories = get_terms( 'product_cat' );
        include_once SMARTWOO_PATH . 'templates/product-admin-temp/add-product.php';

    }

    /**
     * Edit product page
     */
    private static function edit_page() {
        $product_id = isset( $_GET['product_id'] ) ? absint( $_GET['product_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $tab        = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

        if ( empty( $product_id ) ) {
            echo wp_kses_post( smartwoo_error_notice( 'Product ID Parameter must not be manipulated' ) );
            return;
        }
            
        $product = wc_get_product( $product_id );

        if ( $product ) {
            $image_url      = wp_get_attachment_url( $product->get_image_id() ) ? wp_get_attachment_url( $product->get_image_id() ) : wc_placeholder_img_src();
            $product_page   = str_replace( trailingslashit( $product->get_slug() ), '', $product->get_permalink()  );
        }

        $product_categories = get_terms( 'product_cat' );
        $tabs               = array( '' => 'Dashboard' );

        include_once SMARTWOO_PATH . 'templates/product-admin-temp/edit-product.php';
        
    }

    /**
     * Handles product form submission.
     */
    public static function product_form_submit() {
        
        if ( ! check_ajax_referer( 'smart_woo_nonce', 'security', false ) ) {
            wp_send_json_error( array( 'message' => 'Action failed basic authentication.' ), 401 );
        }

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'You do not have permission to perform this action.' ), 403 );
		}

        $errors = self::check_errors();

        if ( ! empty( $errors ) ) {
            wp_send_json_error( array( 'htmlContent' => smartwoo_error_notice( $errors, true ), 'message' => 'Errors' ) ); 
        }

        $product = self::save_product();

        if ( is_wp_error( $product ) ) {
            wp_send_json_error( array( 'htmlContent' => smartwoo_error_notice( $product->get_error_message(), true ), 'message' => 'Invalid or deleted product.' ) ); 
        }

        if (  is_a( $product, 'SmartWoo_Product' ) && $product->get_id() ) {
            $html = '<div class="notice notice-info">
                <p>Product has been created</p>
                <p>View product <a href="' . esc_url( get_permalink( $product->get_id() ) ) . '" target="_blank">HERE</a></p>
                <p> Edit product <a href="' . esc_url( smartwoo_admin_product_url( 'edit', $product->get_id() ) ) . '">HERE</a></p>
            </div>';
            wp_send_json_success( 
                array( 
                    'message'       => 'Product has been saved', 
                    'edit_url'      => smartwoo_admin_product_url( 'edit', $product->get_id() ),
                    'page_url'      => get_permalink( $product->get_id() ),
                    'htmlContent'   => $html
                ) 
            );
        }

        wp_send_json_error( array( 'message' => 'Unable to create product' ), 503 );
    }

    /**
     * Set up form field property and check for errors
     * 
     * @return array
     */
    private static function check_errors() {
        $fields = array();
        $errors = array();
        $fields['smartwoo_product_id']  = isset( $_POST['smartwoo_product_id'] ) ? absint( $_POST['smartwoo_product_id'] ) : 0; 
        $fields['product_name']         = isset( $_POST['product_name'] ) ? sanitize_text_field( wp_unslash( $_POST['product_name'] ) ) : '';
        $fields['product_slug']         = isset( $_POST['product_slug'] ) ? sanitize_text_field( wp_unslash( $_POST['product_slug'] ) ) : '';
        $fields['description']          = isset( $_POST['description'] ) ? wp_kses_post( $_POST['description'] ) : '';
        $fields['regular_price']        = isset( $_POST['regular_price'] ) ? floatval( $_POST['regular_price'] ) : 0;
        $fields['sign_up_fee']          = isset( $_POST['sign_up_fee'] ) ? floatval( $_POST['sign_up_fee'] ) : 0;
        $fields['sale_price']           = isset( $_POST['sale_price'] ) ? floatval( $_POST['sale_price'] ) : 0;
        $fields['date_on_sale_from']    = isset( $_POST['date_on_sale_from'] ) ? sanitize_text_field( wp_unslash( $_POST['date_on_sale_from'] ) ) : '';
        $fields['date_on_sale_to']      = isset( $_POST['date_on_sale_to'] ) ? sanitize_text_field( wp_unslash( $_POST['date_on_sale_to'] ) ) : '';
        $fields['upsell_ids']           = isset( $_POST['upsell_ids'] ) ? array_map( 'absint', wp_unslash( explode( ',', $_POST['upsell_ids'] ) ) ) : array();
        $fields['cross_sell_ids']       = isset( $_POST['cross_sell_ids'] ) ? array_map( 'absint', wp_unslash( explode( ',', $_POST['cross_sell_ids'] ) ) ) : array();
        $fields['short_description']    = isset( $_POST['short_description'] ) ? wp_kses_post( $_POST['short_description'] ) : '';
        $fields['product_status']       = isset( $_POST['product_status'] ) ? sanitize_text_field( $_POST['product_status'] ) : '';
        $fields['visibility']           = isset( $_POST['visibility'] ) ? sanitize_text_field( wp_unslash( $_POST['visibility'] ) ) : '';
        $fields['grace_period_number']  = isset( $_POST['grace_period_number'] ) ? absint( $_POST['grace_period_number'] ) : 0;
        $fields['grace_period_unit']    = isset( $_POST['grace_period_unit'] ) ? sanitize_text_field( wp_unslash( $_POST['grace_period_unit'] ) ) : '';
        $fields['billing_cycle']        = isset( $_POST['billing_cycle'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_cycle'] ) ) : '';
        $fields['product_image_id']     = isset( $_POST['product_image_id'] ) ? absint( $_POST['product_image_id'] ) : 0;
        $fields['product_gallery_ids']  = isset( $_POST['product_gallery_ids'] ) ? array_map( 'absint', wp_unslash( $_POST['product_gallery_ids'] ) ) : array();
        $fields['product_category_ids'] = isset( $_POST['product_category_ids'] ) ? array_map( 'absint', wp_unslash( $_POST['product_category_ids'] ) ) : array();
        $fields['_is_featured']         = isset( $_POST['_is_featured'] );
        $fields['is_sold_individually'] = isset( $_POST['is_sold_individually'] );
        $fields['is_smartwoo_downloadable']     = isset( $_POST['is_smartwoo_downloadable'] );
        $fields['sw_downloadable_file_names']   = isset( $_POST['sw_downloadable_file_names'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['sw_downloadable_file_names'] ) ) : array();
        $fields['sw_downloadable_file_urls']    = isset( $_POST['sw_downloadable_file_urls'] ) ? array_map( 'sanitize_url', wp_unslash( $_POST['sw_downloadable_file_urls'] ) ) : array();
    
        self::instance()->form_fields = $fields;
        $options    = array_keys( wc_get_product_visibility_options() );
        if ( ! in_array( self::instance()->form_fields['visibility'], $options, true ) ) {
			$errors[] =  __( 'Invalid catalog visibility option.', 'smart-woo-service-invoicing' );
		}

        if ( empty( self::instance()->form_fields['product_name'] ) ) {
            $errors[]   = __( 'Product name cannot be empty', 'smart-woo-service-invoicing' );
        }

        if ( ! in_array( self::instance()->form_fields['billing_cycle'], array_keys( smartwoo_supported_billing_cycles() ) ) ) {
            $errors[] = __( 'Billing cycle is not supported', 'smart-woo-service-invoicing' );
        }

        if ( isset( $_POST['smartwoo_product_id'] ) && empty( $_POST['smartwoo_product_id'] ) ) {
            $errors[]   = __( 'Product ID is missing', 'smart-woo-service-invoicing' );
        }
        return $errors;
    
    }

    /**
     * Get the form data
     */
    private function get_form_data() {
        return apply_filters( 'smartwoo_product_form_data', self::instance()->form_fields );
    }

    /**
     * Create or update SmartWoo_Product with form submitted data.
     * 
     * @return SmartWoo_Product|WP_Error $product
     */
    private static function save_product() {
        $form_fields    = self::instance()->get_form_data();
        
        if ( isset( $form_fields['smartwoo_product_id'] ) && ! empty( $form_fields['smartwoo_product_id'] ) ) {
            $product    = wc_get_product( $form_fields['smartwoo_product_id'] );

            if ( ! $product ) {
                return new WP_Error( 'invalid_product', 'This product does not exists anymore' );
            }
        } else {
            $product    = new SmartWoo_Product();
        }
        
        $product->set_name( $form_fields['product_name'] );

        if ( $product->get_id() && ! empty( $form_fields['product_slug'] ) ) {
            $product->set_slug( $form_fields['product_slug'] );
        }
        // Prices.
        $product->set_regular_price( $form_fields['regular_price'] );

        if ( $product->get_id() ) {
            $product->update_sign_up_fee( $form_fields['sign_up_fee'] );

        } else {
            $product->add_sign_up_fee( $form_fields['sign_up_fee'] );
        }
        

        if ( ! empty( $form_fields['date_on_sale_from'] ) ) {
            $product->set_date_on_sale_from( $form_fields['date_on_sale_from'] );
        }

        if ( ! empty( $form_fields['date_on_sale_to'] ) ) {
            $product->set_date_on_sale_to( $form_fields['date_on_sale_to'] );

        }
        
        if ( ! empty( $form_fields['upsell_ids'] ) ) {
            $product->set_upsell_ids( $form_fields['upsell_ids'] );
        }

        if ( ! empty( $form_fields['sale_price'] ) ) {
            $product->set_sale_price( $form_fields['sale_price'] );
        }

        if ( ! empty( $form_fields['cross_sell_ids'] ) ) {
            $product->set_cross_sell_ids( $form_fields['cross_sell_ids'] );

        }

        if ( ! empty( $form_fields['product_category_ids'] ) ) {
            $product->set_category_ids( $form_fields['product_category_ids'] );
        } else {
            $product->set_category_ids( array() );

        }

        $product->set_description( $form_fields['description'] );
        $product->set_short_description( $form_fields['short_description'] );
        
        // Status and visibility.
        $product->set_status( $form_fields['product_status'] );
        $product->set_catalog_visibility( $form_fields['visibility'] );
        $product->set_featured( $form_fields['_is_featured'] );
        $product->set_sold_individually( $form_fields['is_sold_individually'] );

        // Billing cycle and expiration.
        if ( $product->get_id() ) {
            $product->update_billing_cycle( $form_fields['billing_cycle'] );
            $product->update_grace_period_number( $form_fields['grace_period_number'] );
            $product->update_grace_period_unit( $form_fields['grace_period_unit'] );
        } else {
            $product->add_billing_cycle( $form_fields['billing_cycle'] );
            $product->add_grace_period_number( $form_fields['grace_period_number'] );
            $product->add_grace_period_unit( $form_fields['grace_period_unit'] );
        }


        // Product media.
        $product->set_image_id( $form_fields['product_image_id'] );
        $product->set_gallery_image_ids( $form_fields['product_gallery_ids'] );

        if ( $form_fields['is_smartwoo_downloadable'] ) {
            self::set_up_downloads_data( $product );
        } elseif ( $product->is_downloadable() && $product->get_id() ) {
            $product->delete_meta_data( '_smartwoo_product_downloadable_data' );
        } 

        $product->save();
        return $product;
    }

    /**
     * Set up downloadable data.
     * 
     * @param SmartWoo_Product $product
     */
    private static function set_up_downloads_data( &$product ) {
        $file_names = self::instance()->get_form_data()['sw_downloadable_file_names'];
        $file_urls  = self::instance()->get_form_data()['sw_downloadable_file_urls'];

        $downloadables  = array();
        if ( count( $file_names ) === count( $file_urls ) ) {
            $downloadables  = array_combine( $file_names, $file_urls );
        }
        
        foreach ( $downloadables as $k => $v ) {
            if ( empty( $k ) || empty( $v ) ) {
                unset( $downloadables[$k] );
            }
        }

        if ( ! empty( $downloadables ) ) {
            if ( $product->get_id() ) {
                $product->update_downloadable_data( $downloadables );

            } else {
                $product->add_downloadable_data( $downloadables );

            }
        } else {
            $product->delete_meta_data( '_smartwoo_product_downloadable_data' );

        }
    }
}

SmartWoo_Product_Controller::listen();