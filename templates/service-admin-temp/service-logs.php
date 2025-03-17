<?php
/**
 * Service logs template
 * 
 * @author Callistus
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="sw-admin-view-details">
    <?php echo wp_kses_post( smartwoo_sub_menu_nav( $tabs, 'Client','sw-admin', $tab, 'service_id=' . $service_id . '&tab' ) ); ?>

    <?php if( class_exists( 'SmartWooPro', false ) && method_exists( 'SmartWooPro', 'load_service_logs' ) ) : ?>
        <?php call_user_func_array( array( new SmartWooPro(), 'load_service_logs' ), array( $service_id ) ) ?>
    <?php else: ?>
        <?php echo wp_kses_post( smartwoo_pro_feature( 'service logs' ) ); ?>
    <?php endif; ?>
</div>