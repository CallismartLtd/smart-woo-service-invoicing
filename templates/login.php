<?php
/**
 * Portal login page template
 */

defined( 'ABSPATH' ) || exit; ?>

<div class="smartwoo-page">
    <?php echo wp_kses_post( smartwoo_get_navbar( 'Login' ) ); ?>
    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="smartwoo-login-form" id="smartwoo-login-form">
        <div class="smartwoo-login-form-content">
            <div class="smartwoo-login-form-notice">
                <?php echo wp_kses_post( $args['notice'] ); ?>
                <?php if ( $error = smartwoo_get_form_error() ) : ?>
                    <div id="sw-error-div"><?php echo wp_kses_post( $error ); ?></div>
                <?php endif; ?>
            </div>

            <div class="smartwoo-login-form-body">
                <label for="sw-user-login" class="smartwoo-login-form-label">Username/Email *</label>
                <input type="text" id="sw-user-login" class="smartwoo-login-input" name="user_login" />
            </div>

            <div class="smartwoo-login-form-body">
                <label for="sw-user-password" class="smartwoo-login-form-label">Password *</label>
                <input type="password" id="sw-user-password" class="smartwoo-login-input" name="password" />
                <span id="smartwoo-login-form-visible" class="dashicons dashicons-visibility"></span>
                <span id="smartwoo-login-form-invisible" class="dashicons dashicons-hidden" style="display: none"></span>
            </div>

            <?php wp_nonce_field( 'smartwoo_login_nonce', 'smartwoo_login_nonce' ); ?>
            <input type="hidden" name="action" value="smartwoo_login_form" />
            <input type="hidden" name="redirect" value="<?php echo esc_url( $args['redirect'] ); ?>" />
            <input type="hidden" name="referer" value="<?php echo esc_url( wp_get_referer() ); ?>" />
            <div style="display:flex; flex-direction: row; justify-content: space-between;">
                <label style="margin-left:10px;" for="remember_me"> <input id="remember_me" type="checkbox" name="remember_me"/> Remember Me</label>
                <button type="submit" class="sw-blue-button" id="sw-login-btn"><?php echo esc_html( apply_filters( 'smartwoo_login_button_text', __( 'login', 'smart-woo-service-invoicing' ) ) ); ?></button>
            </div>
        </div>
    </form>
</div>
