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
    
    <div class="callismart-app-support-inbox">
        <div id="loader"></div>
        <div class="callismart-app-support-inbox_left">
            <div class="callismart-app-support-inbox_left-header">
                <form class="callismart-app-support-inbox_left-header-form">
                    <select id="bulk-action-select" name="action_type">
                        <option value=""><?php esc_html_e( 'Bulk Actions', 'smart-woo-service-invoicing' ); ?></option>
                        <option value="read"><?php esc_html_e( 'Mark Read', 'smart-woo-service-invoicing' ); ?></option>
                        <option value="unread"><?php esc_html_e( 'Mark Unread', 'smart-woo-service-invoicing' ); ?></option>
                        <option value="delete"><?php esc_html_e( 'Delete', 'smart-woo-service-invoicing' ); ?></option>
                    </select>
                    <button type="submit" class="button" disabled><?php esc_html_e( 'Apply', 'smart-woo-service-invoicing' ); ?></button>
                </form>
                <div class="callismart-app-support-inbox_left-header-quick-actions">
                    <button type="button" class="callismart-app-support-inbox-refresh" data-args="<?php echo esc_attr( smartwoo_json_encode_attr( ['action_type' => 'fetch', 'force' => true] ) ); ?>" title="<?php esc_html_e( 'Refresh', 'smart-woo-service-invoicing' ); ?>"></button>
                    <button type="button" class="callismart-app-support-inbox-markAllRead" data-args="<?php echo esc_attr( smartwoo_json_encode_attr( ['action_type' => 'all_read'] ) ); ?>" title="<?php esc_html_e( 'Mark all read', 'smart-woo-service-invoicing' ); ?>"></button>
                </div>
            </div>
            <hr>
            <ul class="callismart-app-support-inbox_left-messages-list">
              
                <li class="callismart-app-support_message empty-messages" style="<?php printf( '%s', empty( $messages ) ? '' : 'display: none' ); ?>">
                    <svg width="220" height="230" viewBox="0 0 220 250" xmlns="http://www.w3.org/2000/svg">
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
                <li class="masterCheckBox"><button type="button" class="button"><?php esc_html_e( 'Select all', 'smart-woo-service-invoicing' ); ?></button></li>
                <?php foreach ( $messages as $id => $data ) : ?>
                    <li id="message-id-<?php echo esc_attr( $id ); ?>" class="callismart-app-support_message<?php echo esc_attr( $data['read'] ?? false ? ' read' : ' unread' ) ?>" data-message-json="<?php echo esc_attr( smartwoo_json_encode_attr( $data ) ); ?>">
                        <input type="checkbox" id="<?php echo esc_attr( $id ); ?>" class="callismart-app-support-checkbox">
                        <div class="callismart-app-support_message-info">
                            <p class="subject"><?php echo esc_attr( $data['subject'] ?? __( 'No Subject', 'smart-woo-service-invoicing' ) ); ?> <span class="message-time"></span></p>
                            <span class="exerpt"><?php echo esc_html( wp_trim_words( $data['body'] ?? '', 6 ) ); ?></span>
                        </div>
                    </li>
                <?php endforeach; ?>
                
            </ul>
        </div>

        <div class="callismart-app-support-inbox_right">
            <div class="callismart-app-support-inbox_right-header">
                <span id="mobile-close"></span>
            </div>
            <div class="callismart-app-support-inbox_right-message-body">
                <svg xmlns="http://www.w3.org/2000/svg" width="720" height="360" id="noMessageSVG" viewBox="0 0 720 360" role="img" aria-labelledby="clickLeftTitle clickLeftDesc">
                    <defs>
                        <linearGradient id="bgGrad" x1="0" y1="0" x2="1" y2="1">
                        <stop offset="0" stop-color="#f8fafc"/>
                        <stop offset="1" stop-color="#eef2ff"/>
                        </linearGradient>

                        <linearGradient id="cardGrad" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0" stop-color="#ffffff"/>
                        <stop offset="1" stop-color="#fbfdff"/>
                        </linearGradient>

                        <filter id="softShadow" x="-50%" y="-50%" width="200%" height="200%">
                        <feDropShadow dx="0" dy="8" stdDeviation="18" flood-color="#00000022"/>
                        </filter>

                        <filter id="subtle" x="-20%" y="-20%" width="140%" height="140%">
                        <feDropShadow dx="0" dy="6" stdDeviation="10" flood-color="#00000014"/>
                        </filter>
                    </defs>

                    <!-- background -->
                    <rect width="100%" height="100%" rx="14" fill="url(#bgGrad)"/>

                    <!-- left panel (list) -->
                    <g transform="translate(40,36)">
                        <rect x="0" y="0" width="280" height="288" rx="12" fill="url(#cardGrad)" stroke="#e6eef7" filter="url(#subtle)"/>
                        <!-- header -->
                        <rect x="16" y="18" width="160" height="22" rx="6" fill="#eef2ff"/>
                        <!-- small controls -->
                        <circle cx="234" cy="29" r="8" fill="#fff" stroke="#e6eef7"/>
                        <circle cx="256" cy="29" r="8" fill="#fff" stroke="#e6eef7"/>
                        <!-- list items -->
                        <!-- highlighted item -->
                        <g id="item-1">
                        <rect x="16" y="56" width="248" height="56" rx="8" fill="#eef6ff" stroke="#dfe9ff"/>
                        <rect x="28" y="68" width="150" height="14" rx="6" fill="#1f2937"/>
                        <rect x="28" y="88" width="120" height="10" rx="5" fill="#94a3b8"/>
                        </g>
                        <!-- other items -->
                        <g opacity="0.95">
                        <rect x="16" y="128" width="248" height="40" rx="8" fill="#ffffff"/>
                        <rect x="28" y="136" width="140" height="12" rx="6" fill="#334155" opacity="0.9"/>
                        <rect x="28" y="152" width="100" height="8" rx="4" fill="#94a3b8" opacity="0.9"/>

                        <rect x="16" y="176" width="248" height="40" rx="8" fill="#ffffff"/>
                        <rect x="28" y="184" width="130" height="12" rx="6" fill="#334155" opacity="0.9"/>
                        <rect x="28" y="200" width="110" height="8" rx="4" fill="#94a3b8" opacity="0.9"/>

                        <rect x="16" y="224" width="248" height="40" rx="8" fill="#ffffff"/>
                        <rect x="28" y="232" width="100" height="12" rx="6" fill="#334155" opacity="0.9"/>
                        <rect x="28" y="248" width="80" height="8" rx="4" fill="#94a3b8" opacity="0.9"/>
                        </g>

                        <!-- clickable hand (pointer) over highlighted item -->
                        <g transform="translate(188,48)">
                        <!-- subtle pointer shadow -->
                        <ellipse cx="18" cy="64" rx="18" ry="8" fill="#00000010"/>
                        <!-- stylized hand pointer -->
                        <path d="M10 12c1.2-0.7 2.8-0.5 3.8 0.4l14 12c0.9 0.8 1.1 2 0.6 3.1l-8 20c-0.6 1.5-2.3 2.2-3.8 1.6-1.5-0.6-2.1-2.4-1.5-3.9L21 39l-11-9c-1-0.9-1.2-2.5-0.4-3.6 0.8-1.1 2.3-1.4 3.5-0.9z"
                                fill="#2563eb" stroke="#1e40af" stroke-width="0.8" transform="translate(-10,-8) scale(0.9)"/>
                        <!-- small click ripple -->
                        <circle cx="24" cy="34" r="6" fill="#60a5fa" opacity="0.15"/>
                        <circle cx="24" cy="34" r="2.6" fill="#60a5fa" opacity="0.28"/>
                        </g>
                    </g>

                    <!-- right panel (viewer) -->
                    <g transform="translate(380,36)">
                        <rect x="0" y="0" width="280" height="288" rx="12" fill="url(#cardGrad)" stroke="#e6eef7" filter="url(#softShadow)"/>
                        <rect x="20" y="28" width="220" height="18" rx="6" fill="#f1f5f9"/>
                        <rect x="20" y="58" width="160" height="12" rx="6" fill="#cbd5e1"/>
                        <rect x="20" y="80" width="240" height="14" rx="6" fill="#e6eef7"/>
                        <rect x="20" y="106" width="240" height="12" rx="6" fill="#f1f5f9"/>
                        <rect x="20" y="130" width="200" height="12" rx="6" fill="#f1f5f9"/>
                        <rect x="20" y="156" width="220" height="12" rx="6" fill="#f1f5f9"/>
                        <!-- faint empty-state blob -->
                        <g transform="translate(100,220)">
                        <ellipse cx="0" cy="0" rx="86" ry="10" fill="#00000010"/>
                        </g>
                    </g>

                    <!-- arrow linking left item to right viewer -->
                    <g transform="translate(320,150)">
                        <path d="M0 0 C30 0, 50 -30, 90 -30 L140 -30" fill="none" stroke="#475569" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round" opacity="0.9"/>
                        <polygon points="148,-34 140,-30 148,-26" fill="#475569" opacity="0.9"/>
                    </g>

                    <!-- caption -->
                    <text x="360" y="320" text-anchor="middle" fill="#334155" font-family="Inter, sans-serif" font-size="16" font-weight="600">
                        <?php esc_html_e( 'Click a message on the left to view', 'smart-woo-service-invoicing' ); ?>
                    </text>
                </svg>
            </div>

        </div>
    </div>

</div>
