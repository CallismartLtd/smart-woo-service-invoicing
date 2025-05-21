<?php
/**
 * Template file for subscription assets
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="sw-admin-view-details">
    <?php echo wp_kses_post( smartwoo_sub_menu_nav( $tabs, 'Assets <a href="' . smartwoo_service_edit_url( $service_id ) .'">Edit</a>','sw-admin', $tab, 'service_id=' . $service_id . '&tab' ) ); ?>
    <?php if ( empty( $service ) ) : ?>
        <?php echo wp_kses_post( smartwoo_notice( 'Invalid or deleted service subscription <a href="' . esc_url( admin_url( 'admin.php?page=sw-admin' ) ) . '">Back</a>' ) ) ?>

    <?php else : ?>
        <div class="sw-admin-service-assets-header">
            <div class="sw-service-assets-count-box">
                <h1><?php echo absint( $total_assets ); ?></h1>
                <p>Total Assets</p>
            </div>
            <div class="sw-service-assets-count-box">
                <h1><?php echo absint( count( $downloadables ) ); ?></h1>
                <p>Downloadable Files</p>
            </div>
            <div class="sw-service-assets-count-box">
                <h1><?php echo absint( count( $additionals ) ); ?></h1>
                <p>Other Assets</p>
            </div>
        </div>
        <div class="sw-admin-service-assets-body">
            <div class="sw-admin-service-assets-body-nav-container">
                <div class="sw-admin-service-assets-button active">Downloads</div>
                <?php foreach ( $additionals as $asset ) : ?>
                    <div class="sw-admin-service-assets-button"><?php echo esc_html( $asset->get_asset_name() ); ?></div>
                <?php endforeach; ?>
            </div>

            <div class="sw-admin-assets-body-content">
                <?php if( empty( $downloadables ) ) : ?>
                    <p class="sw-not-found"><?php esc_html_e( 'No file found.', 'smart-woo-service-invoicing' ) ?></p>
                <?php else: ?>
                    <div class="heading">
                        <h3><?php echo esc_html( ucfirst( $download_asset_object->get_asset_name() ) ); ?></h3>
                        <p><strong>Limit:</strong> <?php echo esc_html( $download_asset_object->get_access_limit() ); ?></p>
                    </div>
                    <table class="smartwoo-assets-downloads-table" align="center">
                        <thead>
                            <tr>
                                <th></th>
                                <th><?php esc_html_e( 'File Name', 'smart-woo-service-invoicing' ); ?></th>
                                <th><?php esc_html_e( 'Type', 'smart-woo-service-invoicing' ); ?></th>
                                <th><?php esc_html_e( 'Size', 'smart-woo-service-invoicing' ); ?></th>
                                <th><?php esc_html_e( 'Action', 'smart-woo-service-invoicing' ); ?></th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php $id = 1; foreach ( $downloadables as $file_name => $file_url ) : ?>
                                <tr>
                                    <td><?php echo wp_kses_post( $download_asset_object->get_file_icon( $file_url ) ); ?></td>
                                    <td data-label="<?php esc_attr_e( 'File Name', 'smart-woo-service-invoicing' ); ?>"><?php echo esc_html( $file_name ); ?></td>
                                    <td data-label="<?php esc_attr_e( 'Type', 'smart-woo-service-invoicing' ); ?>"><?php echo esc_html( $download_asset_object->get_mime_from_url( $file_url ) ); ?></td>
                                    <td data-label="<?php esc_attr_e( 'Size', 'smart-woo-service-invoicing' ); ?>"><?php echo esc_html( $download_asset_object->get_file_size( $file_url ) ); ?></td>
                                    <td data-label="<?php esc_attr_e( 'Action', 'smart-woo-service-invoicing' ); ?>">
                                        <a href="<?php echo esc_url( smartwoo_download_url( $id, $download_asset_object->get_key(), $download_asset_object->get_id(), $service->get_service_id() ) ); ?>"><?php esc_html_e( 'Download', 'smart-woo-service-invoicing' ); $id++; ?></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
            <?php if ( ! empty( $additionals ) ) : ?>
                <?php foreach( $additionals as $asset ) : ?>
                    <div class="sw-admin-assets-body-content smartwoo-hide">
                        <div class="heading">
                            <h3><?php echo esc_html( ucfirst( $asset->get_asset_name() ) ); ?></h3>
                            <p><strong>Limit:</strong> <?php echo esc_html( $asset->get_access_limit() ); ?></p>
                        </div>
                        <div class="sw-admin-custom-assets-data">
                            <div class="sw-custom-asset-left">
                                <?php echo esc_html( array_key_first( $asset->get_asset_data() ) ); ?>
                            </div>
                            <div class="sw-custom-asset-right">
                                <?php echo wp_kses_post( do_shortcode( $asset->get_data( array_key_first( $asset->get_asset_data() ) ) ) ); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>