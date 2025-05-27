<?php
/**
 *  The front-end invoice main page template
 */
defined( 'ABSPATH' ) || exit; ?>

<div class="smartwoo-page">
	<?php echo wp_kses_post( smartwoo_get_navbar( 'My Invoices', smartwoo_invoice_page_url() ) ); ?>
    <?php echo wp_kses_post( smartwoo_all_user_invoices_count() ); ?>
    <span style="float:right;"><?php smartwoo_table_limit_field( $limit ); ?></span>
	<div class="sw-table-wrapper">
        <table class="sw-table">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Invoice ID', 'smart-woo-service-invoicing' ); ?></th>
                    <th><?php esc_html_e( 'Invoice Date', 'smart-woo-service-invoicing' ); ?></th>
                    <th><?php esc_html_e( 'Date Due', 'smart-woo-service-invoicing' ); ?></th>
                    <th><?php esc_html_e( 'Total', 'smart-woo-service-invoicing' ); ?></th>
                    <th><?php esc_html_e( 'Status', 'smart-woo-service-invoicing' ); ?></th>
                    <th><?php esc_html_e( 'Action', 'smart-woo-service-invoicing' ); ?></th>
                </tr>
            </thead>
            <tbody>
        
                <?php if ( empty( $invoices ) ) : ?>
                    <tr>
                        <td colspan="6" style="text-align: center;"><?php echo esc_html( $not_found_text ); ?></td>
                    </tr>
                <?php else: ?>

                    <?php foreach ( $invoices as $invoice ) : $GLOBALS['product'] = $invoice->get_product(); ?>
                        <tr>
                            <td><?php echo esc_html( $invoice->get_invoice_id() ); ?></td>
                            <td><?php echo esc_html( smartwoo_check_and_format( $invoice->get_date_created() ) ); ?></td>
                            <td><?php echo esc_html( smartwoo_check_and_format( $invoice->get_date_due() ) ); ?></td>
                            <td><?php echo smartwoo_price( $invoice->get_totals(), array( 'currency' => $invoice->get_currency() ) ); ?></td>
                            <td class="payment-status"><?php echo esc_html( ucwords( $invoice->get_status() ) ); ?></td>
                            <td><a href="<?php echo esc_url( $invoice->preview_url() ); ?>" class="invoice-preview-button"><?php echo esc_html__( 'View Details', 'smart-woo-service-invoicing' ); ?></a></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>

            </tbody>
        </table>
        <div style="text-align: right;">
            <?php if ( $total_pages > 1 ) : ?>
                <div class="sw-pagination-buttons">
                    <p><?php echo absint( $total_items_count ) . ' item' . ( $total_items_count > 1 ? 's' : '' ); ?></p>
                        <?php if ( $page > 1 ) : $prev_page = $page - 1; ?>
                            <a class="sw-pagination-button" href="<?php echo esc_url( smartwoo_get_endpoint_url( 'page', $prev_page ) ); ?>"><button><span class="dashicons dashicons-arrow-left-alt2"></span></button></a>
                        <?php endif; ?>
                    <p><?php echo absint( $page ) . ' of ' . absint( $total_pages ); ?></p>
                    <?php if ( $page < $total_pages ) : $next_page = $page + 1; ?>
                        <a class="sw-pagination-button" href="<?php echo esc_url( smartwoo_get_endpoint_url( 'page', $next_page ) ); ?>"><button><span class="dashicons dashicons-arrow-right-alt2"></span></button></a>
                    <?php endif; ?>
                </div>
            <?php elseif( ! empty( $invoices ) ): ?>
                <div class="sw-pagination-buttons">
                    <p><?php echo absint( $total_items_count ) . ' item' . ( $total_items_count > 1 ? 's' : '' ); ?></p>
                </div>
            <?php endif; ?>
        </div>
	</div>
</div>