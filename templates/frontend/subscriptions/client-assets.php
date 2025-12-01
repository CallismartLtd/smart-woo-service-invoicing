<?php
/**
 * Client assets template.
 * 
 * @author Callistus
 * @package SmartWoo\templates
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="sw-client-assets-main">
<?php if ( empty( $service ) ) : ?>
        <?php echo wp_kses_post( smartwoo_notice( 'Invalid or deleted service subscription <a href="' . esc_url( admin_url( 'admin.php?page=sw-admin' ) ) . '">Back</a>' ) ) ?>

    <?php else : ?>
        <div class="sw-client-service-assets-header">
            <div class="sw-service-assets-count-box">
                <h3><?php echo absint( $total_assets ); ?></h3>
                <p>Total Assets</p>
            </div>
            <div class="sw-service-assets-count-box">
                <h3><?php echo absint( count( $downloadables ) ); ?></h3>
                <p>Downloadable Files</p>
            </div>
            <div class="sw-service-assets-count-box">
                <h3><?php echo absint( count( $additionals ) ); ?></h3>
                <p>Other Assets</p>
            </div>
        </div>
        <div class="sw-client-service-assets-body">
            <div class="sw-client-service-assets-body-nav-container">
                <div class="sw-client-service-assets-button active">Downloads</div>
                <?php foreach ( $additionals as $asset ) : ?>
                    <div class="sw-client-service-assets-button"><?php echo esc_html( $asset->get_asset_name() ); ?></div>
                <?php endforeach; ?>
            </div>

            <div class="sw-client-assets-body-content">
                <?php if( empty( $downloadables ) ) : ?>
                    <p class="sw-not-found"><?php esc_html_e( 'No file found.', 'smart-woo-service-invoicing' ) ?></p>
                <?php else: ?>
                    <div class="heading">
                        <h4><?php echo esc_html( ucfirst( $download_asset_object->get_asset_name() ) ); ?></h4>
                        <p><strong>Limit:</strong> <?php echo esc_html( $download_asset_object->get_access_limit() ); ?></p>
                    </div>
                    <table class="smartwoo-assets-downloads-table" align="center" id="smartwoo-front-table">
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
                                    <td data-label="<?php echo esc_html( $file_name ); ?>"><?php echo esc_html( $file_name ); ?></td>
                                    <td data-label="<?php echo esc_html( $download_asset_object->get_mime_from_url( $file_url ) ); ?>"><?php echo esc_html( $download_asset_object->get_mime_from_url( $file_url ) ); ?></td>
                                    <td data-label="<?php echo esc_html( $download_asset_object->get_file_size( $file_url ) ); ?>"><?php echo esc_html( $download_asset_object->get_file_size( $file_url ) ); ?></td>
                                    <td data-label="<?php esc_attr_e( 'Action', 'smart-woo-service-invoicing' ); ?>">
                                        <a href="<?php echo esc_url( smartwoo_download_url( $file_name, $download_asset_object->get_id(), $service->get_service_id() ) ); ?>"><?php esc_html_e( 'Download', 'smart-woo-service-invoicing' ); ?></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
            <?php if ( ! empty( $additionals ) ) : ?>
                <?php foreach( $additionals as $asset ) : ?>
                    <div class="sw-client-assets-body-content smartwoo-hide">
                        <div class="heading">
                            <h4><?php echo esc_html( ucfirst( $asset->get_asset_name() ) ); ?></h4>
                            <p><strong>Limit:</strong> <?php echo esc_html( $asset->get_access_limit() ); ?></p>
                        </div>
                        <div class="sw-admin-custom-assets-data">
                            <div class="sw-custom-asset-left">
                                <?php echo esc_html( array_key_first( $asset->get_asset_data() ) ); ?>
                            </div>
                            <div class="sw-custom-asset-right">
                                <?php echo smartwoo_escape_editor_content( do_shortcode( $asset->get_data( array_key_first( $asset->get_asset_data() ) ) ) ); /** phpcs:ignore  */ ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>