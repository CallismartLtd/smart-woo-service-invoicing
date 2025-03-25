<?php
/**
 * Smart Woo client portal where all service subscriptions are listed.
 * 
 * @author Callistus
 * @package SmartWoo\Templates
 */

defined( 'ABSPATH' ) || exit;
?>

<div id="smartwoo-portal">
    <h1>Welcome to Smart Woo Portal</h1>
    <p>This is a dedicated template that bypasses theme interference.</p>
    
    <?php
        // Render the actual portal UI
        do_action( 'smartwoo_render_portal' );
    ?>
</div>

<?php
