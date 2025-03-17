<?php
/**
 * View stats and metric for a service
 * 
 * @author Callistus
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="sw-admin-view-details">
    <?php echo wp_kses_post( smartwoo_sub_menu_nav( $tabs, 'Usage Metrics','sw-admin', $tab, 'service_id=' . $service_id . '&tab' ) ); ?>
    <?php if( class_exists( 'SmartWooPro', false ) && method_exists( 'SmartWooPro', 'load_usage' ) ) : ?>
        <?php call_user_func_array( array( new SmartWooPro(), 'load_usage' ), array( '', $service_id, true ) ) ?>
    <?php else: ?>
        <?php echo wp_kses_post( smartwoo_pro_feature( 'advanced stats' ) ); ?>
    <?php endif; ?>
</div>