<?php
/**
 * Template file for subscription assets
 */

defined( 'ABSPATH' ) || exit;
?>
<h1>Assets <span class="dashicons dashicons-database-view"></span></h1>
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
            <h2> Asset Name:</h2> <h3> <?php echo esc_html( $asset->get_asset_name() );?></h3>
            <table class="sw-table">
                <thead>
                    <tr>
                        <th>File Name</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( (array) $asset->get_asset_data() as $name => $value ) : ?>
                        <tr>
                            <td>
                                <?php echo esc_html( $name ); ?>
                            </td>
                            <td>
                                <a href="<?php echo esc_url( smartwoo_download_url( $value, $asset->get_key() ) ); ?>"><button>Download</button></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                </tbody>
            </table>
            
        </div>
    <?php endforeach;?>
    
</div>