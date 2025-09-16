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
                    <li id="dashboardBtn" class="active">Dashboard</li>
                    <li id="dashAddNew">Add New</li>
                    <li id="dashOrderBtn">Orders</li>
                    <li id="dashInvoicesBtn">Invoices</li>
                    <li id="dashProductBtn">Products</li>
                    <li id="dashSettingsBtn">Settings</li>
                </ul>
            </div>
            <div class="sw-admin-menu-icon">
                <span class="dashicons dashicons-menu"></span>
            </div>

            <?php if( ! class_exists( 'SmartWooPro', false ) ):?>
                <div class="sw-upgrade-to-pro">
                    <a><?php echo esc_html( apply_filters( 'smartwoo_dash_pro_button_text', __( 'Activate Pro Features', 'smart-woo-service-invoicing' ) ) );?></a>
                </div>
            <?php endif;?>
        </div>
    </div>

    <div id="swloader" style="background-color: #f1f1f100"></div>

    <div class="sw-admin-dash-body">
        <?php do_action( 'smartwoo_admin_header' ) ?>
        <div class="sw-search-container">
           <div class="sw-search-item-container">
                <input type="search" name="sw_service_search" id="sw_service_search" placeholder="Search for services">
                <button id="swSearchBtn" title="click to search"><span class="dashicons dashicons-search"></span></button>
                <div id="search-notification" class="notification-tooltip"></div>
            </div> 
        </div>
        
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
                    <span class="dashicons dashicons-groups"></span>
                    <h3>Active Subscribers</h3>
                </div>
                <hr>
                <div class="sw-admin-dashboard-summary-item_data">
                    <div class="sw-admin-dashboard-summary-item_data-number">
                        120
                    </div>
                    <div class="sw-admin-dashboard-summary-item_data-stat">
                       
                    </div>
                </div>
            </div>
            <div class="sw-admin-dashboard-summary-item">
                <div class="sw-admin-dashboard-summary-item_heading">
                    <span class="dashicons dashicons-calendar-alt"></span>
                    <h3>Expiring Soon</h3>
                </div>
                <hr>
                <div class="sw-admin-dashboard-summary-item_data">
                    <div class="sw-admin-dashboard-summary-item_data-number">
                        15
                    </div>
                    <div class="sw-admin-dashboard-summary-item_data-stat">
                       
                    </div>
                </div>
            </div>
            <div class="sw-admin-dashboard-summary-item">
                <div class="sw-admin-dashboard-summary-item_heading">
                    <span class="dashicons dashicons-cart"></span>
                    <h3>Pending Orders</h3>
                </div>
                <hr>
                <div class="sw-admin-dashboard-summary-item_data">
                    <div class="sw-admin-dashboard-summary-item_data-number">
                        150
                    </div>
                    <div class="sw-admin-dashboard-summary-item_data-stat">
                       
                    </div>
                </div>
            </div>
            <div class="sw-admin-dashboard-summary-item">
                <div class="sw-admin-dashboard-summary-item_heading">
                    <span class="dashicons dashicons-analytics"></span>
                    <h3>Unpaid Invoices</h3>
                </div>
                <hr>
                <div class="sw-admin-dashboard-summary-item_data">
                    <div class="sw-admin-dashboard-summary-item_data-number">
                        9
                    </div>
                    <div class="sw-admin-dashboard-summary-item_data-stat">
                       
                    </div>
                </div>
            </div>
     
        </div>
        <hr>

        <div class="sw-admin-dashboard-interactivity-section">

            <div class="sw-admin-dashboard-interactivity-section_left">
                <div class="sw-admin-dashboard-interactivity-section_service-subscription-lists">
                    <h3 class="sw-service-subscription-lists_heading"><?php esc_html_e( 'Service Subscription List', 'smart-woo-service-invoicing' ); ?></h3>
                    <div class="sw-admin-dashboard-interactivity-section_service-subscription-lists-content">
                        <div class="sw-service-subscription-lists_filters">
                            <button class="button smartwoo-dasboard-filter-button" data-action="all_active_services_table"><?php esc_html_e( 'Active', 'smart-woo-service-invoicing' ); ?></button>
                            <button class="button smartwoo-dasboard-filter-button" data-action="all_active_nr_services_table"><?php esc_html_e( 'Not Renewable', 'smart-woo-service-invoicing' ); ?></button>
                            <button class="button smartwoo-dasboard-filter-button" data-action="all_expired_services_table"><?php esc_html_e( 'Expired', 'smart-woo-service-invoicing' ); ?></button>
                            <button class="button smartwoo-dasboard-filter-button" data-action="all_cancelled_services_table"><?php esc_html_e( 'Cancelled', 'smart-woo-service-invoicing' ); ?></button>
                            <button class="button smartwoo-dasboard-filter-button" data-action="all_suspended_services_table"><?php esc_html_e( 'Suspended', 'smart-woo-service-invoicing' ); ?></button>
                        </div>
                        
                        <div class="sw-service-subscription-lists_table-wrapper">
                            <h3 class="sw-service-subscription-lists_current-heading">All Subscriptions</h3>
                            <table class="sw-table widefat striped">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" name="" id=""></th>
                                        <th><?php esc_html_e( 'ID', 'smart-woo-service-invoicing' ); ?></th>
                                        <th><?php esc_html_e( 'Name', 'smart-woo-service-invoicing' ); ?></th>
                                        <th><?php esc_html_e( 'Service ID', 'smart-woo-service-invoicing' ); ?></th>
                                        <th><?php esc_html_e( 'Status', 'smart-woo-service-invoicing' ); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ( $services as $service ) : ?>
                                        <tr>
                                            <td><input type="checkbox" name="" id=""></td>
                                            <td><?php echo absint( $service->get_id() ); ?></td>
                                            <td><?php echo esc_html( $service->get_name() ); ?></td>
                                            <td><?php echo esc_html( $service->get_service_id() ); ?></td>
                                            <td><?php echo esc_html( smartwoo_service_status( $service ) ) ?></td>
                                            
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>                        
                    </div>

                </div>
                <div class="sw-admin-dashboard-interactivity-section_service-subscription-lists">
                    <h3 class="sw-service-subscription-lists_heading"><?php esc_html_e( 'Active Subscribers', 'smart-woo-service-invoicing' ); ?></h3>
                    <div class="sw-admin-dashboard-interactivity-section_service-subscription-lists-content">
                        
                        <div class="sw-service-subscription-lists_table-wrapper">
                            <table class="sw-table widefat striped">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" name="" id=""></th>
                                        <th><?php esc_html_e( 'ID', 'smart-woo-service-invoicing' ); ?></th>
                                        <th><?php esc_html_e( 'Name', 'smart-woo-service-invoicing' ); ?></th>
                                        <th><?php esc_html_e( 'Service ID', 'smart-woo-service-invoicing' ); ?></th>
                                        <th><?php esc_html_e( 'Status', 'smart-woo-service-invoicing' ); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><input type="checkbox" name="" id=""></td>
                                        <td>1</td>
                                        <td>Comprehensive Asset</td>
                                        <td>Smliser-2025-04938834</td>
                                        <td>Active</td>
                                        
                                    </tr>
                        
                                    <tr>
                                        <td><input type="checkbox" name="" id=""></td>
                                        <td>2</td>
                                        <td>Comprehensive Asset</td>
                                        <td>Smliser-2025-04938834</td>
                                        <td>Active</td>
                                        
                                    </tr>
                        
                                    <tr>
                                        <td><input type="checkbox" name="" id=""></td>
                                        <td>3</td>
                                        <td>Comprehensive Asset</td>
                                        <td>Smliser-2025-04938834</td>
                                        <td>Active</td>
                                        
                                    </tr>
                            
                                    <tr>
                                        <td><input type="checkbox" name="" id=""></td>
                                        <td>4</td>
                                        <td>Comprehensive Asset</td>
                                        <td>Smliser-2025-04938834</td>
                                        <td>Active</td>
                                        
                                    </tr>
                                </tbody>
                            </table>
                        </div>                        
                    </div>

                </div>
            </div>
            <div class="sw-admin-dashboard-interactivity-section_right">
                <div class="sw-admin-dashboard-interactivity-section_right-top">
                    <h3 class="sw-service-subscription-lists_heading"><?php esc_html_e( 'Needs attention', 'smart-woo-service-invoicing' ); ?></h3>
                    <div class="sw-admin-dashboard-interactivity-section_service-subscription-lists-content">
                        <div class="sw-service-subscription-lists_filters">
                            <button class="button smartwoo-dasboard-filter-button" data-action="unPaidInvoice"><?php esc_html_e( 'Unpaid Invoices', 'smart-woo-service-invoicing' ); ?></button>
                            <button class="button smartwoo-dasboard-filter-button" data-action="all_due_services_table"><?php esc_html_e( 'Due', 'smart-woo-service-invoicing' ); ?></button>
                            <button class="button smartwoo-dasboard-filter-button" data-action="all_on_grace_services_table"><?php esc_html_e( 'Grace Period', 'smart-woo-service-invoicing' ); ?></button>
                        </div>

                        <div class="sw-admin-dashboard-interactivity-section_right-top-data-container">
                            <table class="sw-admin-dashboard-urgent-tasks-list">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>ID</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                       <td>Invoice</td>
                                        <td>CINV-4923894948</td>
                                        <td><span class="dashicons dashicons-ellipsis"></span></td>  
                                    </tr>
                                   
                                    <tr>
                                       <td>Invoice</td>
                                        <td>CINV-4923894948</td>
                                        <td><span class="dashicons dashicons-ellipsis"></span></td>  
                                    </tr>
                                   
                                    <tr>
                                       <td>Invoice</td>
                                        <td>CINV-4923894948</td>
                                        <td><span class="dashicons dashicons-ellipsis"></span></td>  
                                    </tr>
                                   
                                    <tr>
                                       <td>Invoice</td>
                                        <td>CINV-4923894948</td>
                                        <td><span class="dashicons dashicons-ellipsis"></span></td>  
                                    </tr>
                                   
                                </tbody>
                            </table>
                        </div>                    
                    </div>
                </div>
                <div class="sw-admin-dashboard-interactivity-section_right-bottom">
                    <div class="sw-admin-dashboard-interactivity-section_right-bottom-left">
                        <h3 class="sw-service-subscription-lists_heading"><?php esc_html_e( 'Invoices', 'smart-woo-service-invoicing' ); ?></h3>
                        <div class="sw-dashboard-invoices-section">
                            <table class="sw-table widefat striped">
                                <thead>
                                    <tr>
                                        <th><?php esc_html_e( 'Invoice ID', 'smart-woo-service-invoicing' ); ?></th>
                                        <th><?php esc_html_e( 'Date', 'smart-woo-service-invoicing' ); ?></th>
                                        <th><?php esc_html_e( 'Status', 'smart-woo-service-invoicing' ); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>CINV-2025-4433245</td>
                                        <td>February 12, 2025</td>
                                        <td>Paid</td>
                                    </tr>
                                    <tr>
                                        <td>CINV-2025-4433245</td>
                                        <td>February 12, 2025</td>
                                        <td>Paid</td>
                                    </tr>
                                    <tr>
                                        <td>CINV-2025-4433245</td>
                                        <td>February 12, 2025</td>
                                        <td>Paid</td>
                                    </tr>
                                    <tr>
                                        <td>CINV-2025-4433245</td>
                                        <td>February 12, 2025</td>
                                        <td>Paid</td>
                                    </tr>
                                    <tr>
                                        <td>CINV-2025-4433245</td>
                                        <td>February 12, 2025</td>
                                        <td>Paid</td>
                                    </tr>
                                </tbody>
                            </table>

                            <a href="<?php echo esc_url( smartwoo_invoice_page_url() ); ?>" class="button"><?php esc_html_e( 'View All', 'smart-woo-service-invoicing' ); ?></a>
                        </div>

                    </div>
                    <div class="sw-admin-dashboard-interactivity-section_right-bottom-right">
                        <h3 class="sw-service-subscription-lists_heading"><?php esc_html_e( 'Activities', 'smart-woo-service-invoicing' ); ?></h3>
                        <div class="sw-dashboard-activities-section">
                            <?php if ( is_callable( [SmartWooPro::class, 'render_dashboard_activitie'] ) ) : ?>
                                <?php call_user_func( [SmartWooPro::class, 'render_dashboard_activitie'] ) ?>
                            <?php else: ?>
                                Activities adverts here
                            <?php endif; ?>

                        </div>
                        
                    </div>
                </div>
            </div>

        </div>

    </div>

    <div class="sw-admin-dash-footer">
        <?php do_action( 'smartwoo_admin_dash_footer' ); ?>        
    </div>
</div>
