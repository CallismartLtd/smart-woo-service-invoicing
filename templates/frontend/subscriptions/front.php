<?php
/**
 * Template Name: Smart Woo Portal
 * Description: A standalone template for the Smart Woo Service portal.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
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
