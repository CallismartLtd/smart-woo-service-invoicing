<?php
/**
 * Plugin tools page template (Enhanced readable version).
 *
 * @author Callistus
 */

defined( 'ABSPATH' ) || exit;

$report = json_decode( $report_json, true );

/**
 * Helper function to render a table row.
 */
function smartwoo_render_table_row( $key, $value ) {
	echo '<tr>';
	echo '<th style="width: 25%;">' . esc_html( ucfirst( str_replace( '_', ' ', $key ) ) ) . '</th>';
	echo '<td>';

	if ( is_array( $value ) ) {
		echo '<table class="widefat striped" style="margin-top: 10px;">';
		echo '<tbody>';
		foreach ( $value as $sub_key => $sub_value ) {
			if ( is_array( $sub_value ) ) {
				smartwoo_render_table_row( $sub_key, $sub_value );
			} else {
				echo '<tr>';
				echo '<th style="width: 30%;">' . esc_html( ucfirst( str_replace( '_', ' ', $sub_key ) ) ) . '</th>';
				echo '<td>' . esc_html( (string) $sub_value ) . '</td>';
				echo '</tr>';
			}
		}
		echo '</tbody>';
		echo '</table>';
	} else {
		echo esc_html( (string) $value );
	}

	echo '</td>';
	echo '</tr>';
}
?>

<div class="smartwoo-admin-page-content">
	<div class="smartwoo-tools-section">
		<h2><?php esc_html_e( 'Smart Woo System Diagnostics', 'smart-woo-service-invoicing' ); ?></h2>
		<p><?php esc_html_e( 'Copy and share this report when contacting support. It contains environment data only — no personal information.', 'smart-woo-service-invoicing' ); ?></p>

		<?php if ( ! empty( $report ) && is_array( $report ) ) : ?>
			<?php foreach ( $report as $section_key => $section_data ) : ?>
				<h3 style="margin-top: 25px;"><?php echo esc_html( ucfirst( str_replace( '_', ' ', $section_key ) ) ); ?></h3>
				<table class="widefat striped">
					<tbody>
						<?php
						if ( is_array( $section_data ) ) {
							foreach ( $section_data as $key => $value ) {
								smartwoo_render_table_row( $key, $value );
							}
						} else {
							echo '<tr><td colspan="2">' . esc_html( (string) $section_data ) . '</td></tr>';
						}
						?>
					</tbody>
				</table>
			<?php endforeach; ?>
		<?php else : ?>
			<p><?php esc_html_e( 'No diagnostic data available.', 'smart-woo-service-invoicing' ); ?></p>
		<?php endif; ?>

		<hr style="margin: 30px 0;">

		<h3><?php esc_html_e( 'Raw JSON Report', 'smart-woo-service-invoicing' ); ?></h3>
		<textarea id="smartwoo-tools-json" class="h" readonly rows="20"><?php echo esc_textarea( $report_json ); ?></textarea>

		<p>
			<button type="button" class="button button-primary" id="smartwoo-copy-json">
				<?php esc_html_e( 'Copy JSON Report', 'smart-woo-service-invoicing' ); ?>
			</button>
		</p>
	</div>
</div>


