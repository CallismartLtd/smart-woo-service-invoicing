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
     * The submenu page controller.
     */
    public static function menu_controller() {
        $tab    = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    
        // Handle different tabs.
        switch ( $tab ) {
            case 'add-new':
                include_once SMARTWOO_PATH . 'templates/product-admin-temp/sw-add-product.php';
                break;
            case 'edit':
                self::edit_product();
                break;
                $product_id = isset( $_GET['product_id'] ) ? absint( $_GET['product_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                    
                if ( empty( $product_id ) ) {
                    echo wp_kses_post( smartwoo_error_notice( 'Product ID Parameter must not be manipulated' ) );
                    return;
                }
                    
                $product_data = wc_get_product( $product_id );
    
                if ( empty( $product_data ) ) {
                    echo wp_kses_post( smartwoo_error_notice( 'You are trying to edit a product that doesn\'t exist, maybe it has been deleted' ) );
                    return;
                }
    
                if ( ! $product_data instanceof SmartWoo_Product ) {
                    echo wp_kses_post( smartwoo_error_notice( 'This is not a service product' ) );
                    return;
                }
    
                $is_downloadable = $product_data->is_downloadable();
                include_once SMARTWOO_PATH . 'templates/product-admin-temp/sw-edit-product.php';
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
}



/**
 * Controls the new service product creation form submission
 */
function smartwoo_process_new_product() {
    
    if ( isset( $_POST['create_sw_product'], $_POST['sw_add_new_product_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sw_add_new_product_nonce'] ) ), 'sw_add_new_product_nonce' ) ) {
		
		$new_product            = new SmartWoo_Product();
        $product_name           = isset( $_POST['product_name'] ) ? sanitize_text_field( wp_unslash( $_POST['product_name'] ) ) : '';
        $product_price          = isset( $_POST['product_price'] ) ? floatval( $_POST['product_price'] ) : 0;
        $sign_up_fee            = isset( $_POST['sign_up_fee'] ) ? floatval( $_POST['sign_up_fee'] ) : 0;
        $short_description      = isset( $_POST['short_description'] ) ? wp_kses_post( wp_unslash( $_POST['short_description'] ) ) : '';
        $description            = isset( $_POST['description'] ) ? wp_kses_post( wp_unslash( $_POST['description'] ) ) : '';
        $billing_cycle          = isset( $_POST['billing_cycle'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_cycle'] ) ) : '';
        $grace_period_unit      = isset( $_POST['grace_period_unit'] ) ? sanitize_text_field( wp_unslash( $_POST['grace_period_unit'] ) ) : '';
        $grace_period_number    = isset( $_POST['grace_period_number'] ) ? absint( $_POST['grace_period_number'] ) : 0;
        $product_image_id       = isset( $_POST['product_image_id'] ) ? absint( $_POST['product_image_id'] ) : 0;
        $is_downloadable        = ! empty( $_POST['sw_downloadable_file_urls'][0] ) && ! empty( $_POST['sw_downloadable_file_names'][0] );


		// Validation.
		$validation_errors = array();

		if ( empty( $product_name ) ) {
			$validation_errors[] = 'Product Name is required';
		}

		if ( ! preg_match( '/^[A-Za-z0-9\s]+$/', $product_name ) ) {
			$validation_errors[] = 'Product name should only contain letters, and numbers.';
		}

		if ( ! empty( $validation_errors ) ) {
            smartwoo_set_form_error( $validation_errors );
            wp_redirect( smartwoo_admin_product_url( 'add-new' ) );
            exit;
		}

		$new_product->set_name( sanitize_text_field( $product_name ) );
        $new_product->set_regular_price( floatval( $product_price ) );
        $new_product->add_sign_up_fee( floatval( $sign_up_fee ) );
        $new_product->set_short_description( wp_kses_post( $short_description ) );
        $new_product->set_description( wp_kses_post( $description ) );
        $new_product->add_billing_cycle( sanitize_text_field( $billing_cycle ) );
        $new_product->add_grace_period_unit( sanitize_text_field( $grace_period_unit ) );
        $new_product->add_grace_period_number( absint( $grace_period_number ) );
        $new_product->set_image_id( $product_image_id );

        // Check for downloadable properties.
        if ( $is_downloadable ) {
            $file_names     = array_map( 'sanitize_text_field', wp_unslash( $_POST['sw_downloadable_file_names'] ) );
            $file_urls      = array_map( 'sanitize_url', wp_unslash( $_POST['sw_downloadable_file_urls'] ) );
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
                $downloadables  = array_map( 'sanitize_text_field', wp_unslash( $downloadables ) );
                $new_product->add_downloadable_data( $downloadables );
            }

        }
        
        
        $result = $new_product->save();

		if ( is_wp_error( $result ) ) {
            smartwoo_set_form_error( $result->get_error_message() );
            wp_redirect( smartwoo_admin_product_url( 'add-new' ) );
            exit;
		}

		// Show success message with product links
		$product_link = get_permalink( $result->get_id() );
		$edit_link    = admin_url( 'admin.php?page=sw-products&tab=edit&product_id=' . $result->get_id() );
		$success = '<div class="notice notice-success is-dismissible"><p>New product created successfully! View your product <a href="' . esc_url( $product_link ) . '" target="_blank">here</a>.</p>
		<p>Edit the product <a href="' . esc_url( $edit_link ) . '">here</a>.</p></div>';
		smartwoo_set_form_success( $success );
        wp_redirect( smartwoo_admin_product_url( 'add-new' ) );
        exit;
		
	}
}

function smartwoo_process_product_edit() {
    // Handle form submission for updating the product
    if ( isset( $_POST['update_service_product'], $_POST['sw_edit_product_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sw_edit_product_nonce'] ) ), 'sw_edit_product_nonce' ) ) {
        $product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ): 0;
        if ( empty( $product_id ) ) {
            wp_die( 'Invalid product.' );
        }

        $update = wc_get_product( $product_id );

        if ( ! $update ) {
            wp_die( 'Invalid or deleted product.' );
        }

        if ( ! $update instanceof SmartWoo_Product ) {
            wp_die( 'Product is not a service product.' );
        }

        $is_downloadable        = ! empty( $_POST['is_smartwoo_downloadable'] ) && ! empty( $_POST['sw_downloadable_file_urls'][0] ) && ! empty( $_POST['sw_downloadable_file_names'][0] ) ? true: false;
        $product_name           = isset( $_POST['product_name'] ) ? sanitize_text_field( wp_unslash( $_POST['product_name'] ) ) : '';
        $product_price          = isset( $_POST['product_price'] ) ? floatval( $_POST['product_price'] ) : 0;
        $sign_up_fee            = isset( $_POST['sign_up_fee'] ) ? floatval( $_POST['sign_up_fee'] ) : 0;
        $short_description      = isset( $_POST['short_description'] ) ? wp_kses_post( wp_unslash( $_POST['short_description'] ) ) : '';
        $description            = isset( $_POST['description'] ) ? wp_kses_post( wp_unslash( $_POST['description'] ) ) : '';
        $billing_cycle          = isset( $_POST['billing_cycle'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_cycle'] ) ) : '';
        $grace_period_unit      = isset( $_POST['grace_period_unit'] ) ? sanitize_text_field( wp_unslash( $_POST['grace_period_unit'] ) ) : '';
        $grace_period_number    = isset( $_POST['grace_period_number'] ) ? absint( $_POST['grace_period_number'] ) : 0;
        $product_image_id       = isset( $_POST['product_image_id'] ) ? absint( $_POST['product_image_id'] ) : 0;

		$validation_errors = array();

		if ( empty( $product_name ) ) {
			$validation_errors[] = 'Product Name is required';
		}

		if ( ! empty( $validation_errors ) ) {

            smartwoo_set_form_error( $validation_errors );
            wp_redirect( smartwoo_admin_product_url( 'edit', $product_id ) );
            exit;
		}

        $update->set_name( sanitize_text_field( $product_name ) );
        $update->set_regular_price( floatval( $product_price ) );
        $update->update_sign_up_fee( floatval( $sign_up_fee ) );
        $update->set_short_description( wp_kses_post( $short_description ) );
        $update->set_description( wp_kses_post( $description ) );
        $update->update_billing_cycle( sanitize_text_field( $billing_cycle ) );
        $update->update_grace_period_unit( sanitize_text_field( $grace_period_unit ) );
        $update->update_grace_period_number( absint( $grace_period_number ) );
        $update->set_image_id( $product_image_id );
        
        // Check for downloadable properties.
        if ( $is_downloadable ) {
            $file_names     = isset( $_POST['sw_downloadable_file_names'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['sw_downloadable_file_names'] ) ) : array();
            $file_urls      = isset( $_POST['sw_downloadable_file_urls'] ) ? array_map( 'sanitize_url', wp_unslash( $_POST['sw_downloadable_file_urls'] ) ): array();
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
                $downloadables  = array_map( 'sanitize_url', wp_unslash( (array) $downloadables ) );
            }

            $update->update_downloadable_data( ! empty( $downloadables ) ? $downloadables : array() );

        } elseif ( $update->is_downloadable() && ! $is_downloadable ) {
            // Not checking the box means the product should no longer be downloadable.
            $update->update_downloadable_data( array() );
        }

        $result = $update->save();

        if ( is_wp_error( $result ) ) {
            smartwoo_set_form_error( $result->get_error_message() );
            wp_redirect( smartwoo_admin_product_url( 'edit', $product_id ) );
            exit;
        }

		// Show success message with product links
		$product_link = get_permalink( $result->get_id() );
		$edit_link    = admin_url( 'admin.php?page=sw-products&tab=edit&product_id=' . $result->get_id() );
		$success = '<div class="notice notice-success is-dismissible"><p>Updated! View your product <a href="' . esc_url( $product_link ) . '" target="_blank">here</a>.</p></div>';
		smartwoo_set_form_success( $success );
        wp_redirect( smartwoo_admin_product_url( 'edit', $product_id ) );
        exit;
    }
}

