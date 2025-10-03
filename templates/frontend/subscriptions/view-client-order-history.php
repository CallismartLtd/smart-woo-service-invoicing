<?php
/**
 * Template for the client order history card.
 * 
 * @author Callistus
 * @package SmartWoo\templates
 */

defined( 'ABSPATH' ) || exit;
?>


<div class="smartwoo-section-card">
    <h3 class="smartwoo-section-card__title"><?php echo esc_html__( 'Order History', 'smart-woo-service-invoicing' ); ?></h3>
    <input type="hidden" name="action" value="smartwoo_get_order_history">
    <input type="hidden" name="page" value="<?php echo absint( $args['page'] + 1 ); ?>">
    <div class="smartwoo-detail-table-wrapper">
        <table class="smartwoo-detail-table">
            <tbody class="smartwoo-detail-table__body">
                <?php if ( empty( $orders ) ) : ?>
                    <tr>
                        <td colspan="2" class="smartwoo-detail-table__value">
                            <?php esc_html_e( 'Order histories will appear here.', 'smart-woo-service-invoicing' ); ?>
                        </td>
                    </tr>
                <?php else : ?>
                    <?php foreach ( $orders as $order ) : ?>
                        <tr class="smartwoo-detail-table__row">
                            <th scope="row" class="smartwoo-detail-table__label">
                                <?php
                                printf(
                                    /* translators: %s: Parent order ID */
                                    __( 'Order: #%s', 'smart-woo-service-invoicing' ),
                                    absint( $order->get_order_id() )
                                );
                                ?>
                            </th>
                            <td class="smartwoo-detail-table__value">
                                <div class="smartwoo-order-item">
                                    <strong class="smartwoo-order-item__product">
                                        <?php echo esc_html( $order->get_product_name() ); ?>
                                    </strong>
                                    <div class="smartwoo-order-item__meta">
                                        <?php
                                        printf(
                                            /* translators: 1: quantity, 2: price */
                                            esc_html__( 'Qty: %1$s | Price: %2$s', 'smart-woo-service-invoicing' ),
                                            absint( $order->get_quantity() ),
                                            smartwoo_price( $order->get_total() )
                                        );

                                        echo '<br />';

                                        printf(
                                            /* translators: %s: order status */
                                            esc_html__( 'Status: %s', 'smart-woo-service-invoicing' ),
                                            esc_html( $order->get_status() )
                                        );

                                        echo '<br />';

                                        printf(
                                            /* translators: %s: order date */
                                            esc_html__( 'Date: %s', 'smart-woo-service-invoicing' ),
                                            esc_html( smartwoo_check_and_format( $order->get_date_created()->format( 'Y-m-d H:i:s' ), true ) )
                                        );

                                        if ( 'awaiting payment' === strtolower( $order->get_status() ) ) {
                                            echo '<br />';
                                            printf(
                                                /* translators: %s: Payment URL */
                                                esc_html__( 'Action:', 'smart-woo-service-invoicing' ) . ' <a href="%s" class="smartwoo-account-button">%s</a>',
                                                esc_url( $order->get_payment_url() ),
                                                esc_html__( 'Pay', 'smart-woo-service-invoicing' )
                                            );
                                        }
                                        ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="sw-button-container">
    <form>
        <button type="submit" class="smartwoo-account-button" <?php echo esc_html( absint( $args['page'] ) > 1 ? '' : 'disabled="true"' ); ?>><?php esc_html_e( 'Prev', 'smart-woo-service-invoicing' ); ?></button>
        <input type="hidden" name="action" value="smartwoo_get_order_history">
        <input type="hidden" name="page" value="<?php echo absint( $args['page'] - 1 ); ?>">
    </form>
    <form>
        <button type="submit" class="smartwoo-account-button" <?php echo esc_html( $total_pages === $page ? 'disabled="true"' : '' ); ?>><?php esc_html_e( 'Next', 'smart-woo-service-invoicing' ); ?></button>
        <input type="hidden" name="action" value="smartwoo_get_order_history">
        <input type="hidden" name="page" value="<?php echo absint( $args['page'] + 1 ); ?>">
    </form>
</div>

