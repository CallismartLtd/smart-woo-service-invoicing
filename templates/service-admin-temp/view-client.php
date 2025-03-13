<?php
/**
 * Template file for client details, client associated with a service and list of services owned by a client.
 * 
 * @author Callistus
 * @package SmartWoo\Admin\templates
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="sw-admin-view-details">
    <?php echo wp_kses_post( smartwoo_sub_menu_nav( $tabs, 'Client Informations','sw-admin', $tab, 'service_id=' . $service_id . '&tab' ) ); ?>

    <?php if ( ! $service ) : ?>

        <?php echo wp_kses_post( smartwoo_notice( 'Invalid or deleted service <a href="' . admin_url( 'admin.php?page=sw-admin' ) . '">back</a>' ) ); ?>

        <?php else : ?>

    <?php endif; ?>
</div>