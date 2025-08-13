<?php
/**
 * Template for the client account logs card.
 * 
 * @author Callistus
 * @package SmartWoo\templates
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="smartwoo-section-card">
    <h3 class="smartwoo-section-card__title"><?php echo esc_html__( 'Account Logs', 'smart-woo-service-invoicing' ); ?></h3>
    <div class="smartwoo-detail-table-wrapper">
        <table class="smartwoo-detail-table">
            <tbody class="smartwoo-detail-table__body">
                <?php
                foreach ( $filtered_log_data as $title => $value ) : ?>
                    <tr class="smartwoo-detail-table__row">
                        <th scope="row" class="smartwoo-detail-table__label"><?php echo esc_html( $title ); ?>:</th>
                        <td class="smartwoo-detail-table__value"><?php echo esc_html( $value ); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
