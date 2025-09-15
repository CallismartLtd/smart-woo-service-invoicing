<?php
/**
 * Invoice management dashboard template file
 * 
 * @author Callistus
 * @package SmartWoo\admin\templates
 */

defined( 'ABSPATH' ) || exit;

?>
<div class="smartwoo-admin-page-content sw-admin-invoice-status-counts">
    <?php foreach ( $status_counts as $name => $count ) : ?>
        <a class="sw-admin-status-item<?php echo esc_attr( ( $name === $status ) ? ' sw-active-border' : '' ) ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=sw-invoices&tab=sort-by&status=' . $name ) ); ?>" data-count="<?php echo absint( $count ); ?>">
           <span><?php echo esc_html( ucfirst( $name ) ); ?></span>
        </a>
    <?php endforeach; ?>
</div>
<?php if ( empty( $all_invoices ) ) : ?>
    <div class="smartwoo-blank-state">
        <h1 class="smartwoo-invoice-svg"></h1>
        <h2>All invoices will appear here.</h2>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=sw-invoices&tab=add-new-invoice' ) ); ?>" class="smartwoo-div-button">Create Invoice</a>
    </div>
<?php else: ?>
    <?php smartwoo_table_limit_field( $limit ); ?>
    <div class="sw-table-wrapper">
        <table class="sw-table" width="95%">
            <thead>
                <tr>
                    <th><input type="checkbox" name="" id="swTableCheckMaster"></th>
                    <th><?php echo esc_html__( 'Invoice ID', 'smart-woo-service-invoicing' ); ?> </th>
                    <th><?php echo esc_html__( 'Invoice Type', 'smart-woo-service-invoicing' ); ?> </th>
                    <th><?php echo esc_html__( 'Status', 'smart-woo-service-invoicing' ); ?> </th>
                    <th><?php echo esc_html__( 'Date Created', 'smart-woo-service-invoicing' ); ?> </th>
                    <th><?php echo esc_html__( 'Actions', 'smart-woo-service-invoicing' ); ?> </th>
                </tr>
            </thead>

            
            <tbody>
                <?php foreach ( $all_invoices as $invoice ) : ?>
                    <tr>
                        <td><input type="checkbox" data-value="<?php echo esc_html( $invoice->get_invoice_id() );?>" class="sw-table-body-checkbox"></td>
                        <td><?php echo esc_html( $invoice->get_invoice_id() ); ?></td>
                        <td><?php echo esc_html( $invoice->get_type() ); ?></td>
                        <td><?php echo esc_html( $invoice->get_status() ); ?></td>
                        <td><?php echo esc_html( $invoice->get_date_created() ); ?></td>
                        <td class="smartwoo-admin-table-td-options">
                            <a class="sw-icon-button-admin" href="<?php echo esc_url( smartwoo_invoice_preview_url( $invoice->get_invoice_id() ) ); ?>" title="<?php esc_html_e( 'Preview', 'smart-woo-service-invoicing' ); ?>"><span class="dashicons dashicons-visibility"></span></a>
                            <a class="sw-icon-button-admin" href="<?php echo esc_url( admin_url( 'admin.php?page=sw-invoices&tab=edit-invoice&invoice_id=' . $invoice->get_invoice_id() ) ); ?>" title="<?php esc_html_e( 'Edit Invoice', 'smart-woo-service-invoicing' ); ?>"><span class="dashicons dashicons-edit"></span></a>
                            <a class="sw-icon-button-admin" href="<?php echo esc_url( $invoice->download_url() ); ?>" title="<?php esc_html_e( 'Download Invoice', 'smart-woo-service-invoicing' ); ?>"><span class="dashicons dashicons-download"></span></a>
                            <?php echo wp_kses_post( smartwoo_delete_invoice_button( $invoice->get_invoice_id() ) ) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            
        </table>
    </div>
    <div id="swloader" style="background-color:#f1f1f100"></div>
    <div class="sw-pagination-buttons">
        <p><?php echo esc_html( count( $all_invoices ) . ' item' . ( ( count( $all_invoices ) > 1 ) ? 's' : '' ) ); ?></p>
        <?php if ( $paged > 1 ) : ?>
            <a href="<?php echo esc_url( add_query_arg( 'paged', $prev ) ); ?>" class="sw-pagination-button"><button><span class="dashicons dashicons-arrow-left-alt2"></span></button></a>
        <?php endif; ?>
        <p><?php echo absint( $paged ) . ' of ' . absint( $total ) ?></p>
        <?php if ( $paged < $total ) : ?>
            <a href="<?php echo esc_url( add_query_arg( 'paged', $next ) ); ?>" class="sw-pagination-button"><button><span class="dashicons dashicons-arrow-right-alt2"></span></button></a>
        <?php endif; ?>
    </div>
<?php endif; ?>
    
