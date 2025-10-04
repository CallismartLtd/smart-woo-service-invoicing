<?php








/**
 * The Smart Woo Admin dashboard template file.
 * 
 * @author Callistus
 * @package SmartWoo\templates
 * @since 2.0.12
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="smartwoo-admin-page-content sw-admin-dashboard" id="sw-admin-dash">
    <div class="sw-admin-dash-header">
        <div class="sw-admin-header-content">
            <!-- Smart Woo Info -->
            <div class="sw-admin-dash-info">
                <img src="<?php echo esc_url( SMARTWOO_DIR_URL . '/assets/images/smart-woo-official-icon.png' ) ?>" alt="Smart Woo Icon">
                <h1><?php echo wp_kses_post( apply_filters( 'smartwoo_dashboard_name', 'Smart Woo' ) );?></h1>
                <em><?php echo esc_html( apply_filters( 'smartwoo_dashboard_version', SMARTWOO_VER ) );?></em>
            </div>

            <!-- Navigation buvttons -->
            <div class="sw-admin-dash-nav">
                <ul>
                    <li id="dashboardBtn" class="active"><?php esc_html_e( 'Dashboard', 'smart-woo-service-invoicing' ); ?></li>
                    <li id="dashAddNew"><?php esc_html_e( 'Add New Service', 'smart-woo-service-invoicing' ); ?></li>
                    <li id="dashOrderBtn"><?php esc_html_e( 'Orders', 'smart-woo-service-invoicing' ); ?></li>
                    <li id="dashInvoicesBtn"><?php esc_html_e( 'Invoices', 'smart-woo-service-invoicing' ); ?></li>
                    <li id="dashProductBtn"><?php esc_html_e( 'Products', 'smart-woo-service-invoicing' ); ?></li>
                    <li id="dashSettingsBtn"><?php esc_html_e( 'Settings', 'smart-woo-service-invoicing' ); ?></li>
                </ul>
            </div>
            <div class="sw-admin-menu-icon">
                <span class="dashicons dashicons-menu"></span>
            </div>

            <?php if ( ! SmartWoo::pro_is_installed() ) : ?>
                <div class="sw-upgrade-to-pro">
                    <a><?php echo esc_html( apply_filters( 'smartwoo_dash_pro_button_text', __( 'Activate Pro Features', 'smart-woo-service-invoicing' ) ) );?></a>
                </div>
            <?php endif;?>
        </div>
    </div>

    <div id="swloader" style="background-color: #f1f1f100"></div>

    <div class="sw-admin-dash-body">
        <?php do_action( 'smartwoo_admin_header' ) ?>
        
        <div class="sw-admin-dashboard-summary-container">
            <div class="sw-admin-dashboard-summary-item">
                <div class="sw-admin-dashboard-summary-item_heading">
                    <span class="dashicons dashicons-forms"></span>
                    <h3><?php esc_html_e( 'Total Services', 'smart-woo-service-invoicing' ); ?></h3>
                </div>
                <hr>
                <div class="sw-admin-dashboard-summary-item_data">
                    <div class="sw-admin-dashboard-summary-item_data-number">
                        <?php echo absint( $total_services ); ?>
                    </div>
                    <div class="sw-admin-dashboard-summary-item_data-stat">
                       
                    </div>
                </div>
            </div>

            <div class="sw-admin-dashboard-summary-item">
                <div class="sw-admin-dashboard-summary-item_heading">
                    <span class="dashicons dashicons-vault"></span>
                    <h3><?php esc_html_e( 'Active Subscriptions', 'smart-woo-service-invoicing' ); ?></h3>
                </div>
                <hr>
                <div class="sw-admin-dashboard-summary-item_data">
                    <div class="sw-admin-dashboard-summary-item_data-number">
                        <?php echo absint( $total_active_services ); ?>
                    </div>
                    <div class="sw-admin-dashboard-summary-item_data-stat">
                       
                    </div>
                </div>
            </div>
            
            <div class="sw-admin-dashboard-summary-item">
                <div class="sw-admin-dashboard-summary-item_heading">
                    <span class="dashicons dashicons-groups"></span>
                    <h3><?php esc_html_e( 'Active Subscribers', 'smart-woo-service-invoicing' ); ?></h3>
                </div>
                <hr>
                <div class="sw-admin-dashboard-summary-item_data">
                    <div class="sw-admin-dashboard-summary-item_data-number">
                        <?php echo absint( $total_active_subscribers ); ?>
                    </div>
                    <div class="sw-admin-dashboard-summary-item_data-stat">
                       
                    </div>
                </div>
            </div>

            <div class="sw-admin-dashboard-summary-item">
                <div class="sw-admin-dashboard-summary-item_heading">
                    <span class="dashicons dashicons-calendar-alt"></span>
                    <h3><?php esc_html_e( 'Expiring Soon', 'smart-woo-service-invoicing' ); ?></h3>
                </div>
                <hr>
                <div class="sw-admin-dashboard-summary-item_data">
                    <div class="sw-admin-dashboard-summary-item_data-number">
                        <?php echo absint( $expiring_soon_count ) ?>
                    </div>
                    <div class="sw-admin-dashboard-summary-item_data-stat">
                       
                    </div>
                </div>
            </div>
            <div class="sw-admin-dashboard-summary-item">
                <div class="sw-admin-dashboard-summary-item_heading">
                    <span class="dashicons dashicons-cart"></span>
                    <h3><?php esc_html_e( 'New Orders', 'smart-woo-service-invoicing' ); ?></h3>
                </div>
                <hr>
                <div class="sw-admin-dashboard-summary-item_data">
                    <div class="sw-admin-dashboard-summary-item_data-number">
                        <?php echo absint( $new_order_count ) ?>
                    </div>
                    <div class="sw-admin-dashboard-summary-item_data-stat">
                       
                    </div>
                </div>
            </div>
            <div class="sw-admin-dashboard-summary-item">
                <div class="sw-admin-dashboard-summary-item_heading">
                    <span class="dashicons dashicons-analytics"></span>
                    <h3><?php esc_html_e( 'Unpaid Invoices', 'smart-woo-service-invoicing' ); ?></h3>
                </div>
                <hr>
                <div class="sw-admin-dashboard-summary-item_data">
                    <div class="sw-admin-dashboard-summary-item_data-number">
                        <?php echo absint( $unpaid_invoices_count ) ?>
                    </div>
                    <div class="sw-admin-dashboard-summary-item_data-stat">
                       
                    </div>
                </div>
            </div>
     
        </div>

        <hr>

        <form class="smartwoo-interactivity-dashboard-search-container">
           <div class="smartwoo-interactivity-search">
                <input type="search" name="search_term" id="smartwoo-search-input" placeholder="Search">
                <select name="search_type" id="search-select">
                    <option value="service"><?php esc_html_e( 'Subscriptions', 'smart-woo-service-invoicing' ); ?></option>
                    <option value="invoice"><?php esc_html_e( 'Invoice', 'smart-woo-service-invoicing' ); ?></option>
                    <option value="order"><?php esc_html_e( 'Order', 'smart-woo-service-invoicing' ); ?></option>
                </select>
                <button type="submit" class="button" id="smartwoo-search-btn" title="click to search"><?php esc_html_e( 'Search', 'smart-woo-service-invoicing' ); ?></button>
            </div> 
        </form>

        <div class="sw-admin-dashboard-interactivity-section">
            <div class="sw-admin-dashboard-interactivity-section_left">
                <div class="sw-admin-dashboard-interactivity-section_service-subscription-lists" data-section="subscriptionList" data-current-filter="allServices">
                    <h3 class="sw-service-subscription-lists_heading"><?php esc_html_e( 'Service Subscription List', 'smart-woo-service-invoicing' ); ?></h3>
                    <div class="sw-admin-dashboard-interactivity-section_service-subscription-lists-content">
                        <div class="sw-service-subscription-lists_filters">
                            <button class="button smartwoo-dasboard-filter-button" data-get-filter="allServices" data-state-args="<?php echo esc_attr( smartwoo_json_encode_attr( $current_args ) ) ?>" disabled="true"><?php esc_html_e( 'All Subscriptions', 'smart-woo-service-invoicing' ); ?></button>
                            <button class="button smartwoo-dasboard-filter-button" data-get-filter="allActiveServices" data-state-args="<?php echo esc_attr( smartwoo_json_encode_attr( $current_args ) ) ?>"><?php esc_html_e( 'Active', 'smart-woo-service-invoicing' ); ?></button>
                            <button class="button smartwoo-dasboard-filter-button" data-get-filter="allActiveNRServices" data-state-args="<?php echo esc_attr( smartwoo_json_encode_attr( $current_args ) ) ?>"><?php esc_html_e( 'Non-Renewing', 'smart-woo-service-invoicing' ); ?></button>
                            <button class="button smartwoo-dasboard-filter-button" data-get-filter="allDueServices" data-state-args="<?php echo esc_attr( smartwoo_json_encode_attr( $current_args ) ) ?>"><?php esc_html_e( 'Due', 'smart-woo-service-invoicing' ); ?></button>
                            <button class="button smartwoo-dasboard-filter-button" data-get-filter="allExpiredServices" data-state-args="<?php echo esc_attr( smartwoo_json_encode_attr( $current_args ) ) ?>"><?php esc_html_e( 'Expired', 'smart-woo-service-invoicing' ); ?></button>
                            <button class="button smartwoo-dasboard-filter-button" data-get-filter="allCancelledServices" data-state-args="<?php echo esc_attr( smartwoo_json_encode_attr( $current_args ) ) ?>"><?php esc_html_e( 'Cancelled', 'smart-woo-service-invoicing' ); ?></button>
                            <button class="button smartwoo-dasboard-filter-button" data-get-filter="allSuspendedServices" data-state-args="<?php echo esc_attr( smartwoo_json_encode_attr( $current_args ) ) ?>"><?php esc_html_e( 'Suspended', 'smart-woo-service-invoicing' ); ?></button>
                        </div>
                        
                        <div class="sw-service-subscription-lists_table-wrapper">
                            <div class="sw-table-heading">
                                <h3 class="sw-service-subscription-lists_current-heading"><?php esc_html_e( 'All Subscriptions', 'smart-woo-service-invoicing' ); ?></h3>
                                <form class="sw-table-bulk-action-container">
                                    <?php smartwoo_service_status_dropdown( '', $service_bulk_action_select_args ); ?>
                                    <button type="submit" class="button">Apply</button>
                                </form>

                            </div>
                            <table class="sw-table has-checkbox widefat">
                                <thead class="<?php echo esc_attr( empty( $services ) ? 'smartwoo-hide' : ''  ); ?>">
                                    <tr>
                                        <th><input type="checkbox" id="<?php echo absint( time() ) ?>" class="serviceListMasterCheckbox"></th>
                                        <th><?php esc_html_e( 'ID', 'smart-woo-service-invoicing' ); ?></th>
                                        <th><?php esc_html_e( 'Name', 'smart-woo-service-invoicing' ); ?></th>
                                        <th><?php esc_html_e( 'Service ID', 'smart-woo-service-invoicing' ); ?></th>
                                        <th><?php esc_html_e( 'Status', 'smart-woo-service-invoicing' ); ?></th>
                                    </tr>
                                </thead>
                                <tbody class="smartwoo-table-content">
                                    <?php if ( empty( $services ) ) : ?>
                                        <tr>
                                            <?php /*translators %s: Create new subscription link. */ ?>
                                            <td class="sw-not-found"><?php printf( __( 'No service subscriptions found. <a href="%s">Create new service</a>', 'smart-woo-service-invoicing'), esc_url( admin_url( 'admin.php?page=sw-admin&tab=add-new-service' ) ) ); ?></td>
                                        </tr>
                                    <?php else : ?>
                                        <?php foreach ( $services as $service ) : ?>
                                            <tr class="smartwoo-linked-table-row" data-url="<?php echo esc_url( $service->preview_url() ) ?>" title="<?php esc_html_e( 'View subscription', 'smart-woo-service-invoicing' ); ?>">
                                                <td><input type="checkbox" id="<?php echo absint( $service->get_id() ); ?>" class="serviceListCheckbox" data-value="<?php echo esc_attr( $service->get_service_id() ) ?>"></td>
                                                <td><?php echo absint( $service->get_id() ); ?></td>
                                                <td><?php echo esc_html( $service->get_name() ); ?></td>
                                                <td><?php echo esc_html( $service->get_service_id() ); ?></td>
                                                <td><?php smartwoo_print_service_status( $service, ['dashboard-status'] ); ?></td>
                                                
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="sw-dashboard-pagination<?php printf( '%s', ( $total_services < 10  || empty( $services ) ) ? ' smartwoo-hide' : '' ); ?>">
                            <button class="sw-pagination-button" data-pagination="<?php echo esc_attr( smartwoo_json_encode_attr( ['page' => 0, 'limit' => $current_args['limit']] ) ); ?>" disabled="true"><span class="dashicons dashicons-arrow-left-alt2" title="<?php esc_html_e( 'Previous Page', 'smart-woo-service-invoicing' ); ?>"></span></button>
                            <button class="sw-pagination-button" data-pagination="<?php echo esc_attr( smartwoo_json_encode_attr( ['page' => 2, 'limit' => $current_args['limit']] ) ); ?>"><span class="dashicons dashicons-arrow-right-alt2" title="<?php esc_html_e( 'Next Page', 'smart-woo-service-invoicing' ); ?>"></span></button>
                        </div>
                    </div>
                </div>

                <div class="sw-admin-dashboard-interactivity-section_service-subscription-lists" data-section="subscribersList">
                    <h3 class="sw-service-subscription-lists_heading"><?php esc_html_e( 'Active Subscribers', 'smart-woo-service-invoicing' ); ?></h3>
                    <div class="sw-admin-dashboard-interactivity-section_service-subscription-lists-content">
                        <div class="sw-service-subscription-lists_table-wrapper">
                            <table class="sw-table widefat">
                                <thead class="<?php echo esc_attr( empty( $active_subscribers ) ? 'smartwoo-hide' : ''  ); ?>">
                                    <tr>
                                        <th></th>
                                        <th><?php esc_html_e( 'Name', 'smart-woo-service-invoicing' ); ?></th>
                                        <th><?php esc_html_e( 'Subscribed Since', 'smart-woo-service-invoicing' ); ?></th>
                                        <th><?php esc_html_e( 'Last Since', 'smart-woo-service-invoicing' ); ?></th>
                                    </tr>
                                </thead>
                                <tbody class="smartwoo-table-content">
                                    <?php  if ( empty( $active_subscribers ) ) : ?>
                                        <tr>
                                            <td class="sw-not-found"><?php esc_html_e( 'No active subscribers found.', 'smart-woo-serivice-invoicing' ); ?></td>
                                        </tr>
                                    <?php else : ?>
                                        <?php foreach( $active_subscribers as $subscriber ) : ?>
                                            <tr class="smartwoo-linked-table-row" data-url="<?php echo esc_url( get_edit_user_link( $subscriber->id ) ) ?>" title="<?php esc_html_e( 'View subscriber', 'smart-woo-service-invoicing' ); ?>">
                                                <td><img class="sw-table-avatar" src="<?php echo esc_url( $subscriber->avatar_url ); ?>" alt="<?php echo esc_attr( $subscriber->name ); ?> photo" width="48" height="48"></td>
                                                <td><?php echo esc_html( $subscriber->name ); ?></td>
                                                <td><?php echo esc_attr( smartwoo_check_and_format( $subscriber->member_since, true ) ); ?></td>
                                                <td><?php echo esc_attr( $subscriber->last_seen ); ?></td>
                                                
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="sw-dashboard-pagination<?php printf( '%s', ( $total_active_subscribers <= 10 ) ? ' smartwoo-hide' : '' ); ?>">
                            <button class="sw-pagination-button" data-pagination="<?php echo esc_attr( smartwoo_json_encode_attr( ['page' => 0, 'limit' => $current_args['limit']] ) ); ?>" disabled="true"><span class="dashicons dashicons-arrow-left-alt2" title="<?php esc_html_e( 'Previous Page', 'smart-woo-service-invoicing' ); ?>"></span></button>
                            <button class="sw-pagination-button" data-pagination="<?php echo esc_attr( smartwoo_json_encode_attr( ['page' => 2, 'limit' => $current_args['limit']] ) ); ?>"><span class="dashicons dashicons-arrow-right-alt2" title="<?php esc_html_e( 'Next Page', 'smart-woo-service-invoicing' ); ?>"></span></button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="sw-admin-dashboard-interactivity-section_right">
                <div class="sw-admin-dashboard-interactivity-section_right-top" data-section="needsAttention" data-current-filter="allUnPaidInvoice">
                    <h3 class="sw-service-subscription-lists_heading"><?php esc_html_e( 'Needs attention', 'smart-woo-service-invoicing' ); ?></h3>
                    <div class="sw-admin-dashboard-interactivity-section_service-subscription-lists-content">
                        <div class="sw-service-subscription-lists_filters">
                            <button class="button smartwoo-dasboard-filter-button" data-get-filter="allUnPaidInvoice" data-state-args="<?php echo esc_attr( smartwoo_json_encode_attr( $current_args ) ) ?>" disabled="true"><?php esc_html_e( 'Unpaid Invoices', 'smart-woo-service-invoicing' ); ?></button>
                            <button class="button smartwoo-dasboard-filter-button" data-get-filter="allNewOrders" data-state-args="<?php echo esc_attr( smartwoo_json_encode_attr( $current_args ) ) ?>"><?php esc_html_e( 'New Orders', 'smart-woo-service-invoicing' ); ?></button>
                            <button class="button smartwoo-dasboard-filter-button" data-get-filter="allDueServices" data-state-args="<?php echo esc_attr( smartwoo_json_encode_attr( $current_args ) ) ?>"><?php esc_html_e( 'Due', 'smart-woo-service-invoicing' ); ?></button>
                            <button class="button smartwoo-dasboard-filter-button" data-get-filter="allOnExpiryThreshold" data-state-args="<?php echo esc_attr( smartwoo_json_encode_attr( $current_args ) ) ?>"><?php esc_html_e( 'Expiring Soon', 'smart-woo-service-invoicing' ); ?></button>
                        </div>

                        <div class="sw-admin-dashboard-interactivity-section_right-top-data-container">
                            <h3 class="sw-service-subscription-lists_current-heading"><?php esc_html_e( 'All Unpaid Invoice', 'smart-woo-service-invoicing' ); ?></h3>
                            <table class="sw-admin-dashboard-urgent-tasks-list">
                                <thead class="<?php echo esc_attr( empty( $pending_invoices ) ? 'smartwoo-hide' : ''  ); ?>">
                                    <tr>
                                        <th><?php esc_html_e( 'Type', 'smart-woo-service-invoicing' ); ?></th>
                                        <th><?php esc_html_e( 'ID', 'smart-woo-service-invoicing' ); ?></th>
                                        <th><?php esc_html_e( 'Actions', 'smart-woo-service-invoicing' ); ?></th>
                                    </tr>
                                </thead>
                                <tbody class="smartwoo-table-content">
                                    <?php if ( ! empty( $pending_invoices ) ) : ?>
                                        <?php foreach( $pending_invoices as $unpaid_inv ) : ?>
                                            <tr class="smartwoo-linked-table-row" data-url="<?php echo esc_url( smartwoo_invoice_preview_url( $unpaid_inv->get_invoice_id() ) ) ?>" title="<?php esc_html_e( 'View invoice', 'smart-woo-service-invoicing' ); ?>">
                                                <td><?php esc_html_e( 'Invoice', 'smart-woo-service-invoicing' ); ?></td>
                                                <td><?php echo esc_html( $unpaid_inv->get_invoice_id() ); ?></td>
                                                <td>
                                                    <div class="smartwoo-options-dots" tabindex="0">
                                                        <ul class="smartwoo-options-dots-items" title="">
                                                            <li data-action="composeEmail" data-args="<?php echo esc_attr( smartwoo_json_encode_attr( self::prepare_modal_mail_data( $unpaid_inv ) ) ) ?>"><?php esc_html_e( 'Compose Email', 'smart-woo-service-invoicing' ); ?></li>
                                                            <li data-action="markAsPaid" data-args="<?php echo esc_attr( smartwoo_json_encode_attr( [ 'invoice_id' => $unpaid_inv->get_invoice_id(), 'filter' => 'markInvoicePaid'] ) ) ?>"><?php esc_html_e( 'Mark as Paid', 'smart-woo-service-invoicing' ); ?></li>
                                                            <li data-action="sendPaymentReminder" data-args="<?php echo esc_attr( smartwoo_json_encode_attr( [ 'invoice_id' => $unpaid_inv->get_invoice_id(), 'filter' => 'sendPaymentReminder'] ) ) ?>"><?php esc_html_e( 'Send payment reminder', 'smart-woo-service-invoicing' ); ?></li>
                                                            <li data-action="viewInvoiceDetails" data-args="<?php echo esc_attr( smartwoo_json_encode_attr( [ 'invoice_details' => $prepare_invoice_args( $unpaid_inv ), 'filter' => 'viewInvoiceDetails'] ) ) ?>"><?php esc_html_e( 'View Invoice Details', 'smart-woo-service-invoicing' ) ?></li>
                                                        </ul>
                                                        <span class="dashicons dashicons-ellipsis" title="<?php esc_html_e( 'Options', 'smart-woo-service-invoicing' ); ?>"></span>
                                                    </div>
                                                </td>  
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="sw-not-found"><?php esc_html_e( 'No unpaid invoices at this time', 'smart-woo-service-invoicing' ); ?></td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="sw-dashboard-pagination<?php printf( '%s', ( $unpaid_invoices_count < 10 ) ? ' smartwoo-hide' : '' ); ?>">
                            <button class="sw-pagination-button" data-pagination="<?php echo esc_attr( smartwoo_json_encode_attr( ['page' => 0, 'limit' => $current_args['limit']] ) ); ?>" disabled="true"><span class="dashicons dashicons-arrow-left-alt2" title="<?php esc_html_e( 'Previous Page', 'smart-woo-service-invoicing' ); ?>"></span></button>
                            <button class="sw-pagination-button" data-pagination="<?php echo esc_attr( smartwoo_json_encode_attr( ['page' => 2, 'limit' => $current_args['limit']] ) ); ?>"><span class="dashicons dashicons-arrow-right-alt2" title="<?php esc_html_e( 'Next Page', 'smart-woo-service-invoicing' ); ?>"></span></button>
                        </div>
                    </div>
                </div>
                <div class="sw-admin-dashboard-interactivity-section_right-bottom">
                    <div class="sw-admin-dashboard-interactivity-section_right-bottom-left" data-section="recentInvoices">
                        <h3 class="sw-service-subscription-lists_heading"><?php esc_html_e( 'Recent Invoices', 'smart-woo-service-invoicing' ); ?></h3>
                        <div class="sw-dashboard-invoices-section">
                            <table class="sw-table widefat">
                                <thead class="<?php echo esc_attr( empty( $recent_invoices ) ? 'smartwoo-hide' : ''  ); ?>">
                                    <tr>
                                        <th><?php esc_html_e( 'Invoice ID', 'smart-woo-service-invoicing' ); ?></th>
                                        <th><?php esc_html_e( 'Date', 'smart-woo-service-invoicing' ); ?></th>
                                        <th><?php esc_html_e( 'Status', 'smart-woo-service-invoicing' ); ?></th>
                                    </tr>
                                </thead>
                                <tbody class="smartwoo-table-content">

                                    <?php if ( empty( $recent_invoices ) ) : ?>
                                        <tr>
                                            <td class="sw-not-found" colspan="3"><?php esc_html_e( 'Recent invoices will be shown here', 'smart-woo-service-invoicing' ); ?></td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach( $recent_invoices as $recent_inv ): ?>
                                            <tr class="smartwoo-linked-table-row" data-url="<?php echo esc_url( smartwoo_invoice_preview_url( $recent_inv->get_invoice_id() ) ) ?>" title="<?php esc_html_e( 'View invoice', 'smart-woo-service-invoicing' ); ?>">
                                                <td><?php echo esc_html( $recent_inv->get_invoice_id() ); ?></td>
                                                <td><?php echo esc_html( smartwoo_check_and_format( $recent_inv->get_date_created() ) ); ?></td>
                                                <td><?php smartwoo_print_invoice_status( $recent_inv ); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                            <a href="<?php echo esc_url( smartwoo_invoice_page_url() ); ?>" class="button"><?php esc_html_e( 'View All', 'smart-woo-service-invoicing' ); ?></a>
                        </div>

                    </div>
                    <div class="sw-admin-dashboard-interactivity-section_right-bottom-right" data-section="activities" data-current-filter="serviceLogs">
                        <h3 class="sw-service-subscription-lists_heading"><?php esc_html_e( 'Activities', 'smart-woo-service-invoicing' ); ?></h3>
                        <div class="sw-dashboard-activities-section">
                            <?php if ( is_callable( [SmartWooPro::class, 'render_dashboard_activities'] ) ) : ?>
                                <?php call_user_func( [SmartWooPro::class, 'render_dashboard_activities'] ) ?>
                            <?php else: ?>
                                TODO: We will render an image for what this section looks like in the pro plugin
                            <?php endif; ?>

                        </div>
                        
                    </div>
                </div>
            </div>

        </div>
    </div>
    <div id="pro-target"></div>

    <div class="smartwoo-modal-frame" data-section="modal">
        <div class="smartwoo-modal-content">
            <button class="smartwoo-modal-close-btn dashicons dashicons-dismiss" title="<?php esc_html_e( 'Close', 'smart-woo-service-invoicing' ); ?>"></button>

            <div class="smartwoo-modal-heading"></div>
            <div class="smartwoo-modal-body"></div>
            <div class="smartwoo-modal-footer"></div>
        </div>

    </div>

    <div class="sw-admin-dash-footer">
        <?php do_action( 'smartwoo_admin_dash_footer' ); ?>        
    </div>
</div>
<?php wp_enqueue_script( 'smartwoo-admin-dashboard' ); ?>