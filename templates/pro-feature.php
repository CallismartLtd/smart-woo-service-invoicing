<?php
/**
 * Renders the pro feature html content.
 * 
 * @author Callistus.
 * @package SmartWoo\campaigns
 */

defined( 'ABSPATH' ) || exit;
$arg = apply_filters( 'smartwoo_pro_template_arg', '' );
echo wp_kses_post( smartwoo_pro_feature( $arg ) );