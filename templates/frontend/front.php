<?php
/**
 * Template Name: Smart Woo Portal
 * Description: A standalone template for the Smart Woo Service portal.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Detect if the active theme is block-based.
$is_block_theme = function_exists( 'wp_is_block_theme' ) ? wp_is_block_theme() : file_exists( get_template_directory() . '/theme.json' );

// Load the correct header.
if ( $is_block_theme ) {
    do_blocks( '<!-- wp:template-part {"slug":"header"} /-->' );
} else {
    get_header();
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
// Load the correct footer.
if ( $is_block_theme ) {
    ?>
    <!-- wp:template-part {"slug":"footer"} /-->
     <?php

    // wp_template_part( 'footer' );
} else {
    get_footer();
}
?>
