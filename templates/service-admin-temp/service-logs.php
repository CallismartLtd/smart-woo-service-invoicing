<?php
/**
 * Service logs template
 * 
 * @author Callistus
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="sw-admin-view-details">
    <?php if( class_exists( 'SmartWooPro', false ) && method_exists( 'SmartWooPro', 'load_service_logs' ) ) : ?>
        <?php call_user_func( array( new SmartWooPro(), 'load_service_logs' ), $service_id ) ?>
    <?php else: ?>
        <?php echo wp_kses_post( smartwoo_pro_feature( 'service logs' ) ); ?>
    <?php endif; ?>
</div>