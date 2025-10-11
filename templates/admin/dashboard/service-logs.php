<?php
/**
 * Service logs template
 * 
 * @author Callistus
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="smartwoo-admin-page-content sw-admin-view-details">
    <?php if ( is_callable( [SmartWooPro::class, 'load_service_logs'] ) ) : ?>
        <?php call_user_func( [SmartWooPro::class, 'load_service_logs'], $service_id ) ?>
    <?php else: ?>
        <?php echo wp_kses_post( smartwoo_pro_feature( 'service logs' ) ); ?>
    <?php endif; ?>
</div>