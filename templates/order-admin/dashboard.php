<?php
/**
 * The order management dashboard template file
 * 
 * @author Callistus
 * @package SmartWoo\admin\templates
 */

defined( 'ABSPATH' ) || exit;
?>
<h1 class="wp-heading-inline">Service Orders</h1>
<?php if ( empty( $orders ) ): ?>
    <div class="smartwoo-blank-state">
        <h1 class="dashicons dashicons-cart"></h1>
        <h2>When you receive a new service order, it will appear here.</h2>
        <a href="<?php echo esc_url( 'https://callismart.com.ng/smart-woo-usage-guide/#managing-orders' ); ?>" class="smartwoo-div-button" target="_blank">Learn more about managing orders</a>
    </div>

<?php else: ?>
    <?php smartwoo_table_limit_field( $limit ); ?>
    <div class="sw-table-wrapper">
        <table class="sw-table">
            <thead>
                <tr>
                <th><input type="checkbox" name="" id="swTableCheckMaster"></th>
                <th>Order ID</th>
                <th>Date Created</th>
                <th>Status</th>
                <th>Service Name</th>
                <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach( $orders as $order ): ?>
                    <tr>
                        <td><input type="checkbox" data-value="<?php echo absint( $order->get_id() );?>" class="sw-table-body-checkbox"></td>
                        <td><?php echo esc_html( $order->get_order_id() . ' | ' . $order->get_id() ); ?></td>
                        <td><?php echo esc_html( $order->get_date_created( 'plain' ) ); ?></td>
                        <td><?php echo esc_html( $order->get_status() ); ?></td>
                        <td><?php echo esc_html( $order->get_service_name() ); ?></td>
                        <td><?php echo wp_kses_post( $btn_text( $order->get_status(), $order->get_id() ) ) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div id="swloader" style="background-color: #f1f1f100"></div>
    <div class="sw-pagination-buttons">
        <p><?php echo esc_html( count( $orders ) . ' item' . ( ( count( $orders ) > 1 ) ? 's' : '' ) ); ?></p>
        <?php if ( $paged > 1 ) : ?>
            <a href="<?php echo esc_url( add_query_arg( 'paged', $prev ) ); ?>" class="sw-pagination-button"><button><span class="dashicons dashicons-arrow-left-alt2"></span></button></a>
        <?php endif; ?>
        <p><?php echo absint( $paged ) . ' of ' . absint( $total ) ?></p>
        <?php if ( $paged < $total ) : ?>
            <a href="<?php echo esc_url( add_query_arg( 'paged', $next ) ); ?>" class="sw-pagination-button"><button><span class="dashicons dashicons-arrow-right-alt2"></span></button></a>
        <?php endif; ?>
    </div>
<?php endif; ?>