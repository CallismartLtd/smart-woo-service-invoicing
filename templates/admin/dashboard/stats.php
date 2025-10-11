<?php
/**
 * View stats and metric for a service
 * 
 * @author Callistus
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="smartwoo-admin-page-content sw-admin-view-details">

    <?php if( is_callable( [SmartWooPro::class, 'load_usage'] ) ) : ?>
        
        <?php call_user_func( [SmartWooPro::class, 'load_usage'], $service_id ) ?>
    <?php else: ?>
        <?php echo wp_kses_post( smartwoo_pro_feature( 'advanced stats' ) ); ?>
    <?php endif; ?>
</div>