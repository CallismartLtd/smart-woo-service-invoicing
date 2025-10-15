<?php
/**
 * SmartWoo Admin Inbox Template
 *
 * This template renders the modern, two-panel inbox interface, now restructured
 * to include a toolbar for bulk actions and inbox refresh functionality.
 *
 * It expects the following variables to be set by SmartWoo_Support_Controller:
 * - $messages (array): All stored inbox messages.
 * - $unread_count (int): Count of unread messages.
 * - $has_consent (bool): User consent status.
 *
 * @package SmartWoo
 * @subpackage Templates
 * @since 1.1.0
 */

defined( 'ABSPATH' ) || exit; ?>

<div class="smartwoo-admin-page-content">
    <div class="callismart-app-support">
        <div class="callismart-app-support_left">
            <div class="callismart-app-support_left-header">
                <form class="callismart-app-support_left-header-form">
                    <select id="bulk-action-select" name="action_type">
                        <option value=""><?php esc_html_e( 'Bulk Actions', 'smart-woo-service-invoicing' ); ?></option>
                        <option value="read"><?php esc_html_e( 'Mark Read', 'smart-woo-service-invoicing' ); ?></option>
                        <option value="unread"><?php esc_html_e( 'Mark Unread', 'smart-woo-service-invoicing' ); ?></option>
                        <option value="delete"><?php esc_html_e( 'Delete', 'smart-woo-service-invoicing' ); ?></option>
                    </select>
                    <button type="submit" class="button" disabled><?php esc_html_e( 'Apply', 'smart-woo-service-invoicing' ); ?></button>
                </form>
                <div class="callismart-app-support_left-header-quick-actions">
                    <button type="button" class="button" data-args="<?php echo esc_attr( smartwoo_json_encode_attr( ['action_type' => 'fetch', 'force' => true] ) ); ?>"><?php esc_html_e( 'Refresh', 'smart-woo-service-invoicing' ); ?></button>
                    <button type="button" class="button" data-args="<?php echo esc_attr( smartwoo_json_encode_attr( ['action_type' => 'all_read'] ) ); ?>"><?php esc_html_e( 'Mark all read', 'smart-woo-service-invoicing' ); ?></button>
                </div>
            </div>

            <ul class="callismart-app-support_left-messages-list">
                <?php if ( empty( $messages ) ) : ?>
                    <li class="callismart-app-support_message empty-messages">
                        <svg width="220" height="230" viewBox="0 0 220 220" xmlns="http://www.w3.org/2000/svg">
                            <defs>
                                <linearGradient id="inboxGradient" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#ffa77eff" />
                                <stop offset="100%" stop-color="#857DFF" />
                                </linearGradient>
                                <filter id="shadow" x="-20%" y="-20%" width="140%" height="140%">
                                <feDropShadow dx="0" dy="4" stdDeviation="6" flood-color="#00000022" />
                                </filter>
                            </defs>

                            <!-- Envelope Box -->
                            <rect x="30" y="70" width="160" height="100" rx="12" fill="url(#inboxGradient)" filter="url(#shadow)" />

                            <!-- Inbox Flap -->
                            <path d="M30 70 L110 130 L190 70 Z" fill="#FFFFFF" opacity="0.9" />

                            <!-- Paper Inside -->
                            <rect x="60" y="40" width="100" height="60" rx="6" fill="#FFFFFF" stroke="#E0E0E0" stroke-width="1.5" />
                            <line x1="75" y1="55" x2="145" y2="55" stroke="#D0D0D0" stroke-width="2" />
                            <line x1="75" y1="70" x2="145" y2="70" stroke="#D0D0D0" stroke-width="2" />
                            <line x1="75" y1="85" x2="120" y2="85" stroke="#D0D0D0" stroke-width="2" />

                            <!-- Shadow Base -->
                            <ellipse cx="110" cy="180" rx="60" ry="12" fill="#00000010" />

                            <!-- Text Below -->
                            <text x="110" y="205" text-anchor="middle" fill="#555" font-family="Segoe UI, sans-serif" font-size="16">
                                <?php esc_html_e( 'Your inbox is empty', 'smart-woo-service-invoicing' ); ?>
                            </text>
                            <text x="110" y="222" text-anchor="middle" fill="#888" font-family="Segoe UI, sans-serif" font-size="13">
                                <?php esc_html_e( 'No new messages at the moment', 'smart-woo-service-invoicing' ); ?>
                            </text>
                        </svg>

                    </li>
                <?php else: ?>
                    <?php foreach ( $messages as $id => $data ) : ?>
                        <li class="callismart-app-support_message<?php echo esc_attr( $data['read'] ?? false ? 'read' : 'unread' ) ?>" data-message-json="<?php echo esc_attr( smartwoo_json_encode_attr( $data ) ); ?>">
                            <input type="checkbox" id="<?php echo esc_attr( $id ); ?>" class="callismart-app-support-checkbox">
                            <div class="callismart-app-support_message-info">
                                <p class="subject"><?php echo esc_attr( $data['subject'] ?? __( 'No Subject', 'smart-woo-service-invoicing' ) ); ?> <span class="message-time"></span></p>
                                <span class="exerpt"><?php echo esc_html( wp_trim_words( $data['body'] ?? '', 6 ) ); ?></span>
                            </div>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>


        <div class="callismart-app-support_right">
            <div class="callismart-app-support_right-header"></div>
            <div class="callismart-app-support_right-message-body"></div>

        </div>
    </div>

</div>

<?php wp_enqueue_script( 'callismart-support' );

