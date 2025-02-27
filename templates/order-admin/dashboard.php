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
    <?php echo wp_kses_post( smartwoo_notice( 'All Service orders will appear here when a customer purchases a service product.' ) ); ?>
<?php else: ?>

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
                    <td><input type="checkbox" data="<?php echo absint( $order->get_id() );?>" class="sw-table-body-checkbox"></td>
                    <td><?php echo esc_html( $order->get_order_id() . ' | ' . $order->get_id() ); ?></td>
                    <td><?php echo esc_html( $order->get_date_created( 'plain' ) ); ?></td>
                    <td><?php echo esc_html( $order->get_status() ); ?></td>
                    <td><?php echo esc_html( $order->get_service_name() ); ?></td>
                    <td><?php echo wp_kses_post( $process_btn( $order->get_status(), $order->get_id() ) ) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>