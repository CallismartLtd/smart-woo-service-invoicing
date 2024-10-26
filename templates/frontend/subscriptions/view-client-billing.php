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
    <p class="smartwoo-container-item"><span>Name: </span><?php echo esc_html( $billingFirstName . ' ' . $billingLastName ); ?></p>
    <p class="smartwoo-container-item"><span>Company Name:</span> <?php echo esc_html( $company_name ); ?></p>
    <p class="smartwoo-container-item"><span>Email Address:</span> <?php echo esc_html( $email ); ?></p>
    <p class="smartwoo-container-item"><span>Phone:</span> <?php echo esc_html( $phone ); ?></p>
    <p class="smartwoo-container-item"><span>Website</span> <?php echo esc_html( $website ); ?></p>
    <p class="smartwoo-container-item"><span>Address</span> <?php echo esc_html( $billingAddress ); ?></p>
    <br>
    <button class="account-button" id="edit-billing-address"><?php echo esc_html__( 'Edit My Billing Address', 'smart-woo-service-invoicing' ); ?></button>
</div>		