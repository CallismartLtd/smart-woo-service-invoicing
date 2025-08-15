<?php
/**
 * Template for the client billing card.
 * 
 * @author Callistus
 * @package SmartWoo\templates
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="smartwoo-section-card">
    <h3 class="smartwoo-section-card__title">Billing Details</h3>
    <div class="smartwoo-detail-table-wrapper">
        <table class="smartwoo-detail-table">
            <tbody class="smartwoo-detail-table__body">
                <tr class="smartwoo-detail-table__row">
                    <th scope="row" class="smartwoo-detail-table__label">Name:</th>
                    <td class="smartwoo-detail-table__value"><?php echo esc_html( $billingFirstName . ' ' . $billingLastName ); ?></td>
                </tr>
                <tr class="smartwoo-detail-table__row">
                    <th scope="row" class="smartwoo-detail-table__label">Company Name:</th>
                    <td class="smartwoo-detail-table__value"><?php echo esc_html( $company_name ); ?></td>
                </tr>
                <tr class="smartwoo-detail-table__row">
                    <th scope="row" class="smartwoo-detail-table__label">Email Address:</th>
                    <td class="smartwoo-detail-table__value"><?php echo esc_html( $email ); ?></td>
                </tr>
                <tr class="smartwoo-detail-table__row">
                    <th scope="row" class="smartwoo-detail-table__label">Phone:</th>
                    <td class="smartwoo-detail-table__value"><?php echo esc_html( $phone ); ?></td>
                </tr>
                <tr class="smartwoo-detail-table__row">
                    <th scope="row" class="smartwoo-detail-table__label">Address:</th>
                    <td class="smartwoo-detail-table__value"><?php echo esc_html( $billingAddress ); ?></td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="smartwoo-section-card__actions">
        <button class="smartwoo-account-button" id="edit-billing-address" data-action="editBilling">
            <?php echo esc_html__( 'Edit Billing Address', 'smart-woo-service-invoicing' ); ?>
        </button>
    </div>
</div>
