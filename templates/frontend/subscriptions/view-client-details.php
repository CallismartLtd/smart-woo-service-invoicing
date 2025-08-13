<?php
/**
 * Template for the client details card.
 * 
 * @author Callistus
 * @package SmartWoo\templates
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="smartwoo-section-card">
    <h3 class="smartwoo-section-card__title"><?php echo esc_html__( 'My Details', 'smart-woo-service-invoicing' ); ?></h3>
    <div class="smartwoo-detail-table-wrapper">
        <table class="smartwoo-detail-table">
            <tbody class="smartwoo-detail-table__body">
                <tr class="smartwoo-detail-table__row">
                    <th scope="row" class="smartwoo-detail-table__label">Full Name:</th>
                    <td class="smartwoo-detail-table__value"><?php echo esc_html( $full_name ); ?></td>
                </tr>
                <tr class="smartwoo-detail-table__row">
                    <th scope="row" class="smartwoo-detail-table__label">Email:</th>
                    <td class="smartwoo-detail-table__value"><?php echo esc_html( $email ); ?></td>
                </tr>
                <tr class="smartwoo-detail-table__row">
                    <th scope="row" class="smartwoo-detail-table__label">Username:</th>
                    <td class="smartwoo-detail-table__value"><?php echo esc_html( $user_name ); ?></td>
                </tr>
                <tr class="smartwoo-detail-table__row">
                    <th scope="row" class="smartwoo-detail-table__label">Account Type:</th>
                    <td class="smartwoo-detail-table__value"><?php echo esc_html( ucwords( $user_role ) ); ?></td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="smartwoo-section-card__actions">
        <button class="smartwoo-account-button" id="edit-account-button" data-action="editMyInfo">
            <?php echo esc_html__( 'Edit My Details', 'smart-woo-service-invoicing' ); ?>
        </button>
    </div>
</div>
