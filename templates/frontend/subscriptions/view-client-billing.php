<?php
/**
 * Template for the client billing card.
 * 
 * @author Callistus
 * @package SmartWoo\templates
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="smartwoo-details-container">
    <h3>Billing Details</h3>
    <div class="smartwoo-container-item">
        <span>Name:</span>
        <p><?php echo esc_html( $billingFirstName . ' ' . $billingLastName ); ?></p>
    </div>

    <div class="smartwoo-container-item">
        <span>Company Name:</span>
        <p><?php echo esc_html( $company_name ); ?></p>
    </div>

    <div class="smartwoo-container-item">
        <span>Email Address:</span>
        <p><?php echo esc_html( $email ); ?></p>
    </div>

    <div class="smartwoo-container-item">
        <span>Phone</span>
        <p><?php echo esc_html( $phone ); ?></p>
    </div>
    
    <div class="smartwoo-container-item">
        <span>Website</span>
        <p><?php echo esc_html( $website ); ?></p>
    </div>

    <div class="smartwoo-container-item">
        <span>Address</span>
        <p><?php echo esc_html( $billingAddress ); ?></p>
    </div>
    <button class="account-button" id="edit-billing-address"><?php echo esc_html__( 'Edit My Billing Address', 'smart-woo-service-invoicing' ); ?></button>
</div>		