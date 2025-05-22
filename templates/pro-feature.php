<?php
/**
 * Renders the pro feature html content.
 * 
 * @author Callistus.
 * @package SmartWoo\campaigns
 */

defined( 'ABSPATH' ) || exit;

echo wp_kses_post( smartwoo_pro_feature( $feature ) );