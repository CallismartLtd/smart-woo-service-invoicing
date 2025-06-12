<?php
/**
 * Client's view subscriptions page.
 * 
 * @author Callistus
 * @package SmartWoo\templates
 * @since 2.0.0
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="smartwoo-page">
    <?php echo wp_kses_post( smartwoo_get_navbar( 'Service Detail', smartwoo_service_page_url() ) ); ?>
    <?php if ( empty( $service ) || ! $service->current_user_can_access() ) : ?>
        <?php echo wp_kses_post( smartwoo_notice( 'This service subscription does not exists, please contact us if you need further assistance.' ) ); ?>
    <?php else: ?>
        <h3 style="text-align: center;"><?php echo esc_html( $service->get_name() );?> <small>(<?php echo esc_html( $status ); ?>)</small></h3>
        <hr>

        <div class="smartwoo-view-sub-details">
            <nav class="nav-tab-wrapper">
                <a href="<?php echo esc_url( smartwoo_service_page_url() );?>" class="sw-blue-button"><span class="dashicons dashicons-admin-home"></span> All Services</a>
                
                <?php if ( 'Due for Renewal' === $status || 'Expired' === $status || 'Grace Period' === $status ) : ?>
                    <a href="" class="smartwoo-service-renew-button" data-service-id="<?php echo esc_attr( $service->get_service_id() ); ?>"><?php echo esc_html( $renew_button_text ); ?></a>
                <?php endif;?>

                <?php if( $service->has_asset() ) : ?>
                    <a id="smartwoo-assets-sub-nav" class="sw-blue-button"><span class="dashicons dashicons-media-archive"></span> Assets</a>
                <?php endif; ?>

                <?php if ( 'Active' === $status && get_option( 'smartwoo_allow_optout/Cancellation', false ) ):?>
                    <a id="sw-service-quick-action" class="sw-blue-button"
                        data-service-name="<?php echo esc_js( wp_json_encode( $service->get_name() ) );?>"
                        data-service-id="<?php echo esc_js( wp_json_encode( $service->get_service_id() ) );?>"
                        ><span class="dashicons dashicons-screenoptions"></span> <?php echo esc_html__( 'Options', 'smart-woo-service-invoicing' );?>
                    </a> 
                <?php endif;?>

                <?php if ( 'Active' === $status || 'Active (NR)' === $status || 'Grace Period' === $status ):?>
                    <?php echo wp_kses_post( $service_button );?>
                <?php endif;?>

                
                <?php foreach ( (array) $buttons as $button ):?>
                    <?php echo wp_kses_post( $button );?>
                <?php endforeach;?>
        
            </nav>

            <!-- The notice container -->
            <?php $service->print_notice( 'expiry', true ); ?>   
            <?php $service->print_notice( 'unpaid_invoice', true ); ?>
        </div>

        <div class="smartwoo-assets-container" id="smartwoo-sub-info">
            <?php do_action( 'smartwoo_before_service_details_page', $service );?>
            <div class="serv-details-card frontend">
                <div id="swloader">Just a moment....</div>
                <h3>Subscription Info</h3>
                <p class="smartwoo-container-item"><span> Status:</span><?php echo esc_html( $status );?></p>
                <p class="smartwoo-container-item"><span> Service ID:</span><?php echo esc_html( $service->get_service_id() ); ?></p>
                <p class="smartwoo-container-item"><span> Service Type:</span><?php echo esc_html( $service->get_type() );?></p>
                <p class="smartwoo-container-item"><span> Product Name:</span><?php echo esc_html( $product_name );?></p>
                <p class="smartwoo-container-item"><span> Billing Cycle:</span><?php echo esc_html( $service->get_billing_cycle() );?></p>
                <p class="smartwoo-container-item"><span> Start Date:</span><?php echo esc_html( smartwoo_check_and_format( $service->get_start_date() ) );?></p>
                <p class="smartwoo-container-item"><span> Next Payment Date:</span><?php echo esc_html( smartwoo_check_and_format( $service->get_next_payment_date() ) );?></p>
                <p class="smartwoo-container-item"><span> End Date:</span><?php echo esc_html( smartwoo_check_and_format( $service->get_end_date() ) );?></p>
                <p class="smartwoo-container-item"><span> Renewal Status:</span><?php echo esc_html( $service->get_renewal_status() );?></p>
                <p class="smartwoo-container-item"><span> Expiry Date:</span><?php echo esc_html( smartwoo_check_and_format( $expiry_date, true ) );?></p>
                
                <!-- Filter to add more details as associative array of title and value is documentend in includes\frontend\service\template.php -->
        
                <?php foreach ( (array) $additional_details  as $title => $value ):?>
                    <p class="smartwoo-container-item"><span><?php echo esc_html( $title );?></span><?php echo esc_html( $value );?></p>
                <?php endforeach;?>

            </div>
            <?php echo wp_kses_post( apply_filters( 'smartwoo_after_service_details_page', '', $service ) );?>
        </div>

        <?php if( $service->has_asset() ) :?>
        <div class="smartwoo-front-assets-container" id="smartwoo-sub-assets">
            <h2 id="my-assets">Assets</h2>
            <?php echo wp_kses_post( $service->get_client_asset_containers() );?>
        </div>
        <?php endif; ?>
    
    <?php endif; ?>
</div>
<br>