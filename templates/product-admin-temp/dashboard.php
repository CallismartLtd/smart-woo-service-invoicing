<?php
/**
 * The Service Product admin dashboard template.
 */

defined( 'ABSPATH' ) || exit;
?>
<?php echo wp_kses_post( smartwoo_sub_menu_nav( $tabs, 'Products', 'sw-products', $tab, 'tab' ) ); ?>
    <div class="invoice-status-counts">
        <?php foreach ( $status_counts as $name => $count ) : ?>
            <div class="sw-admin-status-item<?php echo esc_attr( ( $name === $status ) ? ' sw-active-border' : '' ) ?>">
                <h2><a href="<?php echo esc_url( admin_url( 'admin.php?page=sw-products&tab=sort-by&status=' . $name ) ); ?>"><?php echo esc_html( ucfirst( $name === 'publish' ? 'Published' : $name ) ); ?> <small><?php echo absint( $count ); ?></small></a></h2>
            </div>
        <?php endforeach; ?>
    </div>
<?php if ( empty( $products ) ) : ?>
    <div class="smartwoo-blank-state">
        <h1 class="smartwoo-service-icon"></h1>
        <h2><?php echo esc_html( $not_found_text ) ?></h2>
        <a href="<?php echo esc_url( 'https://callismart.com.ng/smart-woo-usage-guide/#managing-products' ); ?>" class="smartwoo-div-button" target="_blank">Learn more about managing products</a>
    </div>
<?php else : ?>
    <?php smartwoo_table_limit_field( $limit ); ?>
    <div class="sw-table-wrapper">
        <table class="sw-table">
            <thead>
                <tr>
                    <th><input type="checkbox" name="" id="swTableCheckMaster"></th>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Sign Up Fee</th>
                    <th>Billing Circle</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $products as $product ) : ?>
                    <tr>
                        <td><input type="checkbox" data-value="<?php echo absint( $product->get_id() );?>" class="sw-table-body-checkbox"></td>
                        <td>
                            <div class="smartwoo-product-table-name">
                                <p><?php echo esc_html( $product->get_name() ); ?></p>
                                <small>ID: <?php echo esc_html( $product->get_id() ); ?></small>
                            </div>
                        </td>
                        <td><?php echo smartwoo_price( $product->get_price() ); ?></td>
                        <td><?php echo smartwoo_price( $product->get_sign_up_fee() ); ?></td>
                        <td><?php echo esc_html( $product->get_billing_cycle() ); ?></td>
                        <td>
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=sw-products&tab=edit&product_id=' . $product->get_id() ) ); ?>"><button title="Edit Product"><span class="dashicons dashicons-edit"></span></button></a>
                            <a href="<?php echo esc_url( get_permalink( $product->get_id() ) ); ?>"><button title="Preview"><span class="dashicons dashicons-visibility"></span></button></a>
                            <button class="sw-delete-product" data-product-id="<?php echo esc_attr( $product->get_id() ); ?>"><span class="dashicons dashicons-trash"></span></button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div id="swloader" style="background-color:#f1f1f100"></div>
    <div class="sw-pagination-buttons">
        <p><?php echo esc_html( count( $products ) . ' item' . ( ( count( $products ) > 1 ) ? 's' : '' ) ); ?></p>
        <?php if ( $paged > 1 ) : ?>
            <a href="<?php echo esc_url( add_query_arg( 'paged', $prev ) ); ?>" class="sw-pagination-button"><button><span class="dashicons dashicons-arrow-left-alt2"></span></button></a>
        <?php endif; ?>
        <p><?php echo absint( $paged ) . ' of ' . absint( $total ) ?></p>
        <?php if ( $paged < $total ) : ?>
            <a href="<?php echo esc_url( add_query_arg( 'paged', $next ) ); ?>" class="sw-pagination-button"><button><span class="dashicons dashicons-arrow-right-alt2"></span></button></a>
        <?php endif; ?>
    </div>
<?php endif; ?>