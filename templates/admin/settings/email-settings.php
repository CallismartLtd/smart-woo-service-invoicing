<?php
/**
 * Email Settings Template
 * 
 * @author Callistus
 * @package SmartWoo\templates
 */

defined( 'ABSPATH' ) || exit; // Prevent direct access.
?>
<div class="smartwoo-settings-page">
    <h1><span class="dashicons dashicons-email-alt"></span> <?php esc_html_e( 'Email', 'smart-woo-service-invoicing' ) ?></h1>
    <p>
        <span style="color: red;" class="dashicons dashicons-warning"></span>
        <?php esc_html_e( 'Having trouble with emails not reaching recipients? Try connecting your email address to your domain and setting up SMTP to improve deliverability.', 'smart-woo-service-invoicing' ); ?>
    </p>

    <?php do_action( 'smartwoo_before_email_options' ); ?>

    <form method="post" class="smartwoo-settings-form">
        <h3 style="text-align: center;"><?php esc_html_e( 'Configure which Email is sent', 'smart-woo-service-invoicing' )?></h3>
        <table class="sw-table" style="box-shadow: none;border-radius: 0px; width: 100%">
            <thead >
                <tr>
                    <th><?php esc_html_e( 'Email Type', 'smart-woo-service-invoicing' ); ?></th>
                    <th><?php esc_html_e( 'Recipient(s)', 'smart-woo-service-invoicing' ); ?></th>
                    <th></th>
                </tr>
            </thead>

            <?php foreach ( $options as  $id => $data  ) : ?>
                <tr>
                    <td><strong><?php echo esc_html( $data['title'] ); ?></strong></td>
                    <td><?php echo esc_html( isset( $data['recipient'] ) ? $data['recipient'] : 'N/A' ); ?></td>
                    <td>
                        <?php smartwoo_get_switch_toggle( array( 'id' => $id, 'name'  => $id, 'checked' => boolval( get_option( $id, 0 ) ) ) ); ?>
                        <?php if ( isset( $data['previewable'] ) && $data['previewable'] ): ?>
                            <span style="margin-left: 20px;"></span><a href="<?php echo esc_attr( SmartWoo_Mail::get_preview_url( $id ) ); ?>" class="sw-icon-button-admin" title="Preview" target="_blank"><span class="dashicons dashicons-visibility"></span></a>
                        <?php endif; ?>
                        <?php if ( isset( $data['editable'] ) && $data['editable'] ): ?>
                            <a tempname="<?php echo esc_attr( $id ); ?>" title="Edit template" class="sw-icon-button-admin <?php echo ( $pro_installed ) ? 'sw-edit-mail' : 'sw-edit-mail-nopro' ?>"><span class="dashicons dashicons-edit"></span></a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <?php echo wp_kses_post( smartwoo_pro_feature( 'more-email-options' ) );?>
        <?php wp_nonce_field( 'sw_email_option_nonce', 'sw_email_option_nonce' ); ?>
        <!-- Sender Name -->
        <div class="sw-form-row">
            <label for="smartwoo_email_sender_name" class="sw-form-label"><?php esc_html_e( 'Sender Name','smart-woo-service-invoicing' ); ?></label>
            <span class="sw-field-description" title="This will be the sender name on the mail header">?</span>
            <input type="text" name="smartwoo_email_sender_name" id="smartwoo_email_sender_name" value="<?php echo esc_attr( $sender_name ); ?>" placeholder="eg, Billing Team" class="sw-form-input">
        </div>

        <!-- Email Image header -->
        <div class="sw-form-row">
            <label for="smartwoo_email_image_header" class="sw-form-label"><?php esc_html_e( 'Email Header Image','smart-woo-service-invoicing' ); ?></label>
            <span class="sw-field-description" title="Paste the URL of the image you want to show in the email header">?</span>
            <input type="url" name="smartwoo_email_image_header" id="smartwoo_email_image_header" value="<?php echo esc_attr( $email_image ); ?>" placeholder="eg example.com/image" class="sw-form-input">
        </div>

        <!-- Billing Email -->
        <div class="sw-form-row">
            <label for="smartwoo_billing_email" class="sw-form-label"><?php esc_html_e( 'Billing Email', 'smart-woo-service-invoicing' ) ?></label>
            <span class="sw-field-description" title="This email will be used to send emails to the clients">?</span>
            <input type="email" name="smartwoo_billing_email" id="smartwoo_billing_email" value="<?php echo esc_attr( $billing_email ); ?>" placeholder="eg, billing@domain.com" class="sw-form-input">
        </div>

        <?php do_action( 'smartwoo_after_email_options' ) ?>

        <input type="submit" class="sw-blue-button" name="sw_save_email_options" value="Save Changes">

    </form>
</div>