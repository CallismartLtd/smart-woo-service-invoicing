<?php
/**
 * Template for the client payment details card.
 * 
 * Variables passed from the controller:
 * @var array $payment_details
 * @var array $payment_display
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="smartwoo-section-card">
    <h3 class="smartwoo-section-card__title">
        <?php echo esc_html__( 'Payment Methods', 'smart-woo-service-invoicing' ); ?>
    </h3>
    <div class="smartwoo-detail-table-wrapper">
        <table class="smartwoo-detail-table">
            <tbody class="smartwoo-detail-table__body">

                <!-- Primary -->
                <tr class="smartwoo-detail-table__row">
                    <th scope="row" class="smartwoo-detail-table__label">
                        <?php esc_html_e( 'Primary:', 'smart-woo-service-invoicing' ); ?>
                    </th>
                    <td class="smartwoo-detail-table__value">
                        <?php if ( ! empty( $payment_details['primary'] ) ) : ?>
                            <?php echo wp_kses_post( $payment_display['primary'] ); ?>
                            <button class="smartwoo-account-button smartwoo-inline-edit" 
                                    data-action="editPrimaryPayment">
                                <?php esc_html_e( 'Edit Primary', 'smart-woo-service-invoicing' ); ?>
                            </button>
                        <?php else : ?>
                            <span class="smartwoo-notice-text">
                                <?php esc_html_e( 'Not set', 'smart-woo-service-invoicing' ); ?>
                            </span>
                            <button class="smartwoo-account-button smartwoo-inline-edit" 
                                    data-action="editPrimaryPayment">
                                <?php esc_html_e( 'Add Primary', 'smart-woo-service-invoicing' ); ?>
                            </button>
                        <?php endif; ?>
                    </td>
                </tr>

                <!-- Backup -->
                <tr class="smartwoo-detail-table__row">
                    <th scope="row" class="smartwoo-detail-table__label">
                        <?php esc_html_e( 'Backup:', 'smart-woo-service-invoicing' ); ?>
                    </th>
                    <td class="smartwoo-detail-table__value">
                        <?php if ( ! empty( $payment_details['backup'] ) ) : ?>
                            <?php echo wp_kses_post( $payment_display['backup'] ); ?>
                            <button class="smartwoo-account-button smartwoo-inline-edit" 
                                    data-action="editBackupPayment">
                                <?php esc_html_e( 'Edit Backup', 'smart-woo-service-invoicing' ); ?>
                            </button>
                        <?php else : ?>
                            <span class="smartwoo-notice-text">
                                <?php esc_html_e( 'Not set', 'smart-woo-service-invoicing' ); ?>
                            </span>
                            <button class="smartwoo-account-button smartwoo-inline-edit" 
                                    data-action="editBackupPayment">
                                <?php esc_html_e( 'Add Backup', 'smart-woo-service-invoicing' ); ?>
                            </button>
                        <?php endif; ?>
                    </td>
                </tr>

            </tbody>
        </table>
    </div>
</div>

