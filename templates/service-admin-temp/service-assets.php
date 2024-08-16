<?php
/**
 * Template file for subscription assets
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="smartwoo-assets-container">
    
    <?php if ( empty( $assets ) ):?>
        <div class="serv-details-card">
            <p class="smartwoo-container-item"><span>No Asset found for this service</span></p>
        </div>
    <?php return; endif;?>
    
    <?php foreach( $assets as $asset ):?>
        <div class="serv-details-card">
            <p class="smartwoo-container-item"><span> Asset ID: </span> <?php echo esc_html( $asset->get_id() );?></p>
            <p class="smartwoo-container-item"><span> Access Limit: </span> <?php echo esc_html( $asset->get_access_limit() );?></p>
            <p> Asset Name:</p>
            <h3> <?php echo esc_html( $asset->get_asset_name() );?></h3>
            <table class="sw-table">
                <thead>
                    <tr>
                        <th><?php echo ( 'downloads' === $asset->get_asset_name() ) ? 'File Name': 'Name';?></th>
                        <th> <?php echo ( 'downloads' === $asset->get_asset_name() ) ? 'Action': 'Value';?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $id = 1; foreach ( (array) $asset->get_asset_data() as $name => $value ) : ?>
                        <tr>
                            <td>
                                <?php echo esc_html( $name ); ?>
                            </td>
                            <td>
                            <?php echo ( 'downloads' === $asset->get_asset_name() ) ? '<a href="' . esc_url( smartwoo_download_url( $id, $asset->get_key(), $asset->get_id(), $service->getServiceId() ) ) . '" class="sw-red-button">Download</a>': $value;  $id++;?>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                </tbody>
            </table>
            
        </div>
    <?php endforeach;?>
    
</div>