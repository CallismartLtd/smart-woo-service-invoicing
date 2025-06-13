<?php
/**
 * Admin menu file.
 * 
 * @author Callistus
 * @since 1.0.0
 * @package SmartWoo\Admin
 */

defined( 'ABSPATH' ) || exit; // Prevent direct access.

require_once SMARTWOO_PATH . 'includes/admin/admin-callback-functions.php';

/**
 * The Smart Woo Admin Menu class.
 */
class SmartWoo_Admin_Menu {
	/**
	 * The admin menu screen ID.
	 * 
	 * @var string $admin_screen_id
	 */
	private $admin_screen_id;

	/**
	 * The Service Order screen ID.
	 * 
	 * @var string $service_order_screen_id
	 */
	private $service_order_screen_id;

	/**
	 * The Invoices screen ID.
	 * 
	 * @var string $invoices_screen_id
	 */
	private $invoices_screen_id;

	/**
	 * The Service Products screen ID.
	 * 
	 * @var string $products_screen_id
	 */
	private $products_screen_id;

	/**
	 * The Settings screen ID.
	 * 
	 * @var string $settings_screen_id
	 */
	private $settings_screen_id;

	/**
	 * Singleton instance of this class.
	 */
	private static $instance = null;

	/**
	 * Instantiate a singleton instance of this class.
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Run admin hooks
	 */
	public static function listen() {
		add_action( 'admin_menu', array( __CLASS__, 'register_admin_menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'add_help_tabs' ) );
	}

	public static function register_admin_menu() {
		$self = self::instance();
		$self->admin_screen_id = add_menu_page(
			'Smart Woo',
			'Smart Woo',
			'manage_options',
			'sw-admin',
			array( 'SmartWoo_Dashboard_Controller', 'menu_controller' ),
			'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZlcnNpb249IjEuMSIgd2lkdGg9IjYwMCIgaGVpZ2h0PSI2MDAiIHZpZXdCb3g9IjAgMCAxMDgwIDEwODAiPg0KICAgIDwhLS0gQ2lyY2xlIC0tPg0KICAgIDxnIHRyYW5zZm9ybT0idHJhbnNsYXRlKDU0MCw1NDApIj4NCiAgICAgICAgPGNpcmNsZSBjeD0iMCIgY3k9IjAiIHI9IjUxMiIgDQogICAgICAgICAgICAgICAgc3R5bGU9InN0cm9rZTpyZ2IoMTU0LCAxNjAsIDE2NSk7IHN0cm9rZS13aWR0aDogMTU7IGZpbGw6IHRyYW5zcGFyZW50OyIgDQogICAgICAgICAgICAgICAgdmVjdG9yLWVmZmVjdD0ibm9uLXNjYWxpbmctc3Ryb2tlIi8+DQogICAgPC9nPg0KDQogICAgPCEtLSBTVyBUZXh0IChVc2luZyBmb3JlaWduT2JqZWN0IHRvIGVuc3VyZSBpdCdzIG9uIHRvcCkgLS0+DQogICAgPGZvcmVpZ25PYmplY3QgeD0iMjAiIHk9IjMwMCIgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSI+DQogICAgICAgIDxkaXYgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGh0bWwiIA0KICAgICAgICAgICAgIHN0eWxlPSJmb250LWZhbWlseTogQWxlZ3JleWE7IGZvbnQtc2l6ZTogNTAwcHg7IGZvbnQtd2VpZ2h0OiBib2xkOyANCiAgICAgICAgICAgICAgICAgICAgY29sb3I6IHJnYigwLCAwLCAwKTsgdGV4dC1hbGlnbjogY2VudGVyOyI+DQogICAgICAgICAgICBTVw0KICAgICAgICA8L2Rpdj4NCiAgICA8L2ZvcmVpZ25PYmplY3Q+DQo8L3N2Zz4NCg==',
			58.5
		);

		$new_order_count = smartwoo_count_unprocessed_orders();
		$self->service_order_screen_id = add_submenu_page(
			'sw-admin',
			'Service Orders',
			! empty( $new_order_count ) ? 'Service Orders <span class="awaiting-mod">' . $new_order_count . '</span>': 'Service Orders',
			'manage_options',
			'sw-service-orders',
			array( 'SmartWoo_Orders_Controller', 'menu_controller' )
		);

		$self->invoices_screen_id  = add_submenu_page(
			'sw-admin',
			'Invoices',
			'Invoices',
			'manage_options',
			'sw-invoices',
			array( 'SmartWoo_Invoice_Controller', 'menu_controller' ),
		);

		$self->products_screen_id = add_submenu_page(
			'sw-admin',
			'Service Products',
			'Service Products',
			'manage_options',
			'sw-products',
			array( 'SmartWoo_Product_Controller', 'menu_controller' )
		);

		$self->settings_screen_id = add_submenu_page(
			'sw-admin',
			'General Settings',
			'Settings',
			'manage_options',
			'sw-options',
			array( 'SmartWoo_Settings_Controller', 'menu_controller' )
		);

	}

	/**
	 * Add help tabs to the admin screens.
	 */
	public static function add_help_tabs() {
		add_action( 'load-' . self::instance()->admin_screen_id, array( __CLASS__, 'load_help_tabs') );
		add_action( 'load-' . self::instance()->service_order_screen_id, array( __CLASS__, 'load_help_tabs' ) );
		add_action( 'load-' . self::instance()->invoices_screen_id, array( __CLASS__, 'load_help_tabs' ) );
		add_action( 'load-' . self::instance()->products_screen_id, array( __CLASS__, 'load_help_tabs' ) );
		add_action( 'load-' . self::instance()->settings_screen_id, array( __CLASS__, 'load_help_tabs' ) );
	}

	/**
	 * Help screen callback.
	 */
	public static function load_help_tabs() {
		$screen = get_current_screen();

		if ( self::is_screen( $screen->id, 'settings' ) ) {
			$screen->add_help_tab(
				array(
					'id'		=> 'smartwoo_options_help',
					'title'		=> __( 'Settings', 'smart-woo-service-invoicing' ),
					'callback'	=> array( __CLASS__, 'docs_settings_guide' )
				)
			);
		}
		$screen->add_help_tab( 
			array(
				'id'	=> 'smartwoo_help',
				'title'	=> __( 'Support', 'smart-woo-service-invoicing' ),
				'callback' => 'smartwoo_help_container',
			)
		);

		$screen->add_help_tab( 
			array(
				'id'	=> 'smartwoo_bug_report',
				'title'	=> __( 'Bug Report', 'smart-woo-service-invoicing' ),
				'callback' => 'smartwoo_bug_report_container',
			)
		);

		$screen->add_help_tab( 
			array(
				'id'	=> 'smartwoo_support',
				'title'	=> __( 'Support Our Work', 'smart-woo-service-invoicing' ),
				'callback' => 'smartwoo_support_our_work_container',
			)
		);
	}

	/**
	 * Check whether the screen ID matches any of our screens.
	 * 
	 * @param string $id The screen ID.
	 * @param string $context The context of the screen.
	 */
	public static function is_screen( $value, $context ) {
		$allowed_values = array(
			'admin'				=> self::instance()->admin_screen_id,
			'service_orders' 	=> self::instance()->service_order_screen_id,
			'invoices'			=> self::instance()->invoices_screen_id,
			'products'			=> self::instance()->products_screen_id,
			'settings'			=> self::instance()->settings_screen_id,
		);

		return isset( $allowed_values[ $context ] ) && $allowed_values[ $context ] === $value;
		
	}

	/**
	 * Guide to the settings page.
	 */
	public static function docs_settings_guide() {
		?>
		<h1>Smart Woo Settings</h1>
		<p>Here, you can configure your desired options for your business, invoices, emails, and advanced settings.</p>
		<p>Please check out our documentation for this page <a href="https://callismart.com.ng/smart-woo-usage-guide/#configuring-settings" target="_blank">HERE</a>.</p>
		<?php
	}

	/**
	 * Prints the mordern navigation menu.
	 * 
	 * @param string $title			The page title.
	 * @param array  $menu_options	Associative arrays of button_text => options{
	 * - id		=> The button ID attribute
	 * - class	=> The button class attribute
	 * - href	=> The button url
	 * - active => The value for the active state in the URL query param
	 * },
	 * @param string $query_key The key to get the active button, default false
	 */
	public static function print_mordern_submenu_nav( $title, $menu_options, $query_key = false ) {
		$add_active_class	= is_string( $query_key );
		?>
			<div class="sw-admin-dash-header">
				<div class="sw-admin-header-content">
					<!-- Smart Woo Info -->
					<div class="sw-admin-dash-info">
						<h1><?php echo wp_kses_post( $title );?></h1>
					</div>

					<!-- Navigation buttons -->
					<div class="sw-admin-dash-nav">
						<ul>
							<?php foreach ( $menu_options as $title => $options ) : 
								$found_class	= ( $add_active_class && isset( $_GET[$query_key] ) ) ? sanitize_key( wp_unslash( $_GET[$query_key] ) ) : '';
								$class			= isset( $options['active'] ) && $found_class === $options['active'] ? 'active' : '';
								$class			= isset( $options['class'] ) ? $class . ' ' . $options['class'] : $class;
								?>
								<li class="<?php echo esc_attr( $class ); ?>" id="<?php echo esc_attr( isset( $options['id'] ) ? $options['id'] : '' ); ?>"><a href="<?php echo esc_url( $options['href'] ); ?>"><?php echo esc_html( $title ); ?></a></li>
							<?php endforeach; ?>
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
			<div style="margin-top: 100px"></div>

		<?php
	}

}

SmartWoo_Admin_Menu::listen();