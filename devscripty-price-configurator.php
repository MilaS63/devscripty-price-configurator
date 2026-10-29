<?php
/**
 * Plugin Name: DevScripty Price Configurator
 * Description: Price configurator ([devscripty_configurator]) and a summary of the selected options on the quote page ([devscripty_quote_summary]). No AJAX/REST requests and no hardcoded domain, so it moves from staging to production without changes.
 * Version: 1.2.1
 * Author: DevScripty
 * License: GPL-2.0-or-later
 * Text Domain: dspc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'DSPC_VERSION', '1.2.1' );
define( 'DSPC_URL', plugin_dir_url( __FILE__ ) );

/* Quote page URL. No hardcoded domain: home_url() returns the staging or live address automatically. */
function dspc_quote_url() {
	$quote_url = home_url( '/request-a-quote/' );
	return esc_url_raw( apply_filters( 'dspc_quote_url', $quote_url ) );
}

function dspc_configurator_url() {
	return esc_url( apply_filters( 'dspc_configurator_url', home_url( '/#estimator' ) ) );
}

/* ------------------------------------------------------------------
 * Default settings
 * platforms: Name|Price|Icon (optional)   * after the name = selected by default
 * addons:    Name|Price|Description
 * speeds:    Name|Surcharge|Short label under the slider
 * ---------------------------------------------------------------- */
function dspc_defaults() {
	return array(
		'currency'      => '$',
		'currency_code' => 'USD',
		'low_pct'       => 5,
		'high_pct'      => 15,
		'cta_text'      => 'Submit Proposal Request',
		'title'         => 'Estimated Investment Range',
		'note'          => 'Includes direct developer communication, clean responsive code, basic SEO configuration, and 30 days post-launch technical warranty.',
		'features'      => "100% Transparent Pricing — No hidden fees\nMilestone payments available",
		'load_fonts'    => 1,
		'platforms'     => "WordPress*|900|wordpress\nShopify|1100|shopify\nLanding Page|600|rocket\nGoHighLevel|850|network",
		'addons'        => "Custom UI/UX Design|350|Bespoke tailored mockups\nE-commerce / Cart setup|400|Payment gateways & products\nSpeed Optimization (90+)|250|Core Vitals tuning\nGHL / CRM Automation|300|Workflows & email sequences",
		'speeds'        => "Express (1 Week)|300|Express (1 Wk)\nStandard (2 - 3 Weeks)*|0|Standard (2-3 Wks)\nRelaxed (4+ Weeks)|-100|Relaxed (4+ Wks)",
	);
}

function dspc_get( $key ) {
	$opts = get_option( 'dspc_options', array() );
	$def  = dspc_defaults();
	if ( isset( $opts[ $key ] ) && '' !== $opts[ $key ] ) {
		return $opts[ $key ];
	}
	return isset( $def[ $key ] ) ? $def[ $key ] : '';
}

function dspc_parse( $text ) {
	$out = array();
	foreach ( preg_split( '/\r\n|\r|\n/', (string) $text ) as $line ) {
		$line = trim( $line );
		if ( '' === $line ) {
			continue;
		}
		$p = array_map( 'trim', explode( '|', $line ) );
		if ( count( $p ) < 2 || ! is_numeric( $p[1] ) ) {
			continue;
		}
		$label   = $p[0];
		$default = false;
		if ( '*' === substr( $label, -1 ) ) {
			$default = true;
			$label   = trim( substr( $label, 0, -1 ) );
		}
		$out[] = array(
			'label'   => $label,
			'price'   => (float) $p[1],
			'desc'    => isset( $p[2] ) ? $p[2] : '',
			'default' => $default,
		);
	}
	return $out;
}

function dspc_default_index( $items ) {
	foreach ( $items as $i => $item ) {
		if ( $item['default'] ) {
			return $i;
		}
	}
	return 0;
}

function dspc_money( $n ) {
	return dspc_get( 'currency' ) . number_format( round( $n ) );
}

function dspc_signed_money( $n ) {
	if ( 0 == $n ) {
		return dspc_get( 'currency' ) . '0';
	}
	return ( $n > 0 ? '+' : '-' ) . dspc_get( 'currency' ) . number_format( abs( round( $n ) ) );
}

/* ------------------------------------------------------------------
 * Icons (inline SVG, no Font Awesome and no external requests)
 * ---------------------------------------------------------------- */
function dspc_icon( $key, $label = '' ) {
	$key = strtolower( trim( $key ) );
	if ( '' === $key ) {
		$l = strtolower( $label );
		if ( false !== strpos( $l, 'wordpress' ) ) {
			$key = 'wordpress';
		} elseif ( false !== strpos( $l, 'shopify' ) ) {
			$key = 'shopify';
		} elseif ( false !== strpos( $l, 'landing' ) ) {
			$key = 'rocket';
		} elseif ( false !== strpos( $l, 'highlevel' ) || false !== strpos( $l, 'ghl' ) ) {
			$key = 'network';
		} else {
			$key = 'code';
		}
	}
	$icons = array(
		'wordpress' => '<circle cx="12" cy="12" r="9.5"/><path d="M6.5 8.5L9.5 16.5 12 10l2.5 6.5 3-8"/>',
		'shopify'   => '<path d="M6 8h12l1 12H5L6 8z"/><path d="M9 8V7a3 3 0 0 1 6 0v1"/><path d="M14.5 11.5c-.6-.6-3.4-.9-3.4.8 0 1.8 3.4 1 3.4 2.9 0 1.6-3 1.5-3.7.6"/>',
		'rocket'    => '<path d="M12 3c3 2 5 5 5 9l-2 3H9l-2-3c0-4 2-7 5-9z"/><circle cx="12" cy="10" r="1.6"/><path d="M9 15l-2.5 3.5M15 15l2.5 3.5M12 15v4"/>',
		'network'   => '<rect x="3" y="4" width="6" height="5" rx="1"/><rect x="15" y="4" width="6" height="5" rx="1"/><rect x="9" y="15" width="6" height="5" rx="1"/><path d="M6 9v2.5h12V9M12 11.5V15"/>',
		'code'      => '<path d="M8 8l-4 4 4 4M16 8l4 4-4 4M13.5 5l-3 14"/>',
		'star'      => '<path d="M12 3l2.7 5.7 6.3.8-4.6 4.3 1.2 6.2L12 17l-5.6 3 1.2-6.2L3 9.5l6.3-.8z"/>',
	);
	if ( ! isset( $icons[ $key ] ) ) {
		$key = 'code';
	}
	return '<svg class="dsp-icon" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $icons[ $key ] . '</svg>';
}

/* ------------------------------------------------------------------
 * Enqueue styles/scripts (static files)
 * ---------------------------------------------------------------- */
add_action( 'wp_enqueue_scripts', function () {
	wp_register_style( 'dspc', DSPC_URL . 'assets/css/configurator.css', array(), DSPC_VERSION );
	wp_register_script( 'dspc', DSPC_URL . 'assets/js/configurator.js', array(), DSPC_VERSION, true );
	wp_register_script( 'dspc-prefill', DSPC_URL . 'assets/js/prefill.js', array(), DSPC_VERSION, true );
	wp_register_style( 'dspc-fonts', 'https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap', array(), null );
} );

function dspc_enqueue_style() {
	if ( (int) dspc_get( 'load_fonts' ) ) {
		wp_enqueue_style( 'dspc-fonts' );
	}
	wp_enqueue_style( 'dspc' );
}

/* ------------------------------------------------------------------
 * Shortcode 1: configurator  [devscripty_configurator]
 * ---------------------------------------------------------------- */
add_shortcode( 'devscripty_configurator', 'dspc_shortcode' );

function dspc_shortcode( $atts ) {
	$atts = shortcode_atts( array( 'title' => '' ), $atts, 'devscripty_configurator' );

	$platforms = dspc_parse( dspc_get( 'platforms' ) );
	$addons    = dspc_parse( dspc_get( 'addons' ) );
	$speeds    = dspc_parse( dspc_get( 'speeds' ) );
	if ( empty( $platforms ) ) {
		return '';
	}

	dspc_enqueue_style();
	wp_enqueue_script( 'dspc' );

	$def_platform = dspc_default_index( $platforms );
	$def_speed    = dspc_default_index( $speeds );

	$speed_cfg = array();
	foreach ( $speeds as $s ) {
		$speed_cfg[] = array( 'label' => $s['label'], 'price' => $s['price'] );
	}
	$config = array(
		'currency' => dspc_get( 'currency' ),
		'lowPct'   => (float) dspc_get( 'low_pct' ),
		'highPct'  => (float) dspc_get( 'high_pct' ),
		'quoteUrl' => dspc_quote_url(),
		'speeds'   => $speed_cfg,
	);

	$uid      = 'dspc' . wp_unique_id();
	$features = array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) dspc_get( 'features' ) ) ) );
	$check    = '<svg class="dsp-feat-icon" viewBox="0 0 20 20" width="18" height="18" aria-hidden="true"><circle cx="10" cy="10" r="9" fill="#059669"/><path d="M5.8 10.4l2.8 2.8 5.2-5.6" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
	$arrow    = '<svg class="dsp-cta-arrow" viewBox="0 0 16 16" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 8h12M9 3l5 5-5 5"/></svg>';

	ob_start();
	?>
	<div class="dsp-cfg" data-config="<?php echo esc_attr( wp_json_encode( $config ) ); ?>">

		<div class="dsp-grid">
			<div class="dsp-controls">

				<div class="dsp-step" role="group" aria-labelledby="<?php echo esc_attr( $uid ); ?>-s1">
					<div class="dsp-step-head"><span class="dsp-step-title" id="<?php echo esc_attr( $uid ); ?>-s1">1. <?php esc_html_e( 'Select Primary Platform', 'dspc' ); ?></span></div>
					<div class="dsp-platforms">
						<?php foreach ( $platforms as $i => $item ) : ?>
							<label class="dsp-opt">
								<input type="radio" name="<?php echo esc_attr( $uid ); ?>-platform" data-group="platform" data-index="<?php echo (int) $i; ?>" value="<?php echo esc_attr( $item['price'] ); ?>" <?php checked( $i === $def_platform ); ?>>
								<span class="dsp-opt-body">
									<?php echo dspc_icon( $item['desc'], $item['label'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?>
									<span class="dsp-opt-title"><?php echo esc_html( $item['label'] ); ?></span>
								</span>
							</label>
						<?php endforeach; ?>
					</div>
				</div>

				<?php if ( $addons ) : ?>
				<div class="dsp-step" role="group" aria-labelledby="<?php echo esc_attr( $uid ); ?>-s2">
					<div class="dsp-step-head"><span class="dsp-step-title" id="<?php echo esc_attr( $uid ); ?>-s2">2. <?php esc_html_e( 'Choose Required Add-ons', 'dspc' ); ?></span></div>
					<div class="dsp-addons">
						<?php foreach ( $addons as $i => $item ) : ?>
							<label class="dsp-addon">
								<input type="checkbox" class="dsp-check" data-group="addon" data-index="<?php echo (int) $i; ?>" value="<?php echo esc_attr( $item['price'] ); ?>" <?php checked( $item['default'] ); ?>>
								<span class="dsp-addon-text">
									<span class="dsp-addon-title"><?php echo esc_html( $item['label'] ); ?></span>
									<?php if ( $item['desc'] ) : ?>
										<span class="dsp-addon-desc"><?php echo esc_html( $item['desc'] ); ?></span>
									<?php endif; ?>
								</span>
							</label>
						<?php endforeach; ?>
					</div>
				</div>
				<?php endif; ?>

				<?php if ( count( $speeds ) > 1 ) : ?>
				<div class="dsp-step" role="group" aria-labelledby="<?php echo esc_attr( $uid ); ?>-s3">
					<div class="dsp-step-head dsp-step-head--split">
						<span class="dsp-step-title" id="<?php echo esc_attr( $uid ); ?>-s3">3. <?php esc_html_e( 'Delivery Speed', 'dspc' ); ?></span>
						<span class="dsp-speed-current" aria-live="polite"></span>
					</div>
					<input type="range" class="dsp-range" data-group="speed" min="0" max="<?php echo (int) ( count( $speeds ) - 1 ); ?>" step="1" value="<?php echo (int) $def_speed; ?>" aria-labelledby="<?php echo esc_attr( $uid ); ?>-s3">
					<div class="dsp-speed-scale">
						<?php foreach ( $speeds as $item ) : ?>
							<span><?php echo esc_html( $item['desc'] ? $item['desc'] : $item['label'] ); ?></span>
						<?php endforeach; ?>
					</div>
				</div>
				<?php endif; ?>

			</div>

			<aside class="dsp-result">
				<div class="dsp-result-top">
					<span class="dsp-result-label"><?php echo esc_html( $atts['title'] ? $atts['title'] : dspc_get( 'title' ) ); ?></span>
					<div class="dsp-total" aria-live="polite">
						<span class="dsp-amount">&hellip;</span>
						<?php if ( dspc_get( 'currency_code' ) ) : ?>
							<span class="dsp-code"><?php echo esc_html( dspc_get( 'currency_code' ) ); ?></span>
						<?php endif; ?>
					</div>
					<p class="dsp-note"><?php echo esc_html( dspc_get( 'note' ) ); ?></p>
				</div>
				<div class="dsp-result-bottom">
					<?php if ( $features ) : ?>
					<ul class="dsp-features">
						<?php foreach ( $features as $feat ) : ?>
							<li><?php echo $check; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php echo esc_html( $feat ); ?></span></li>
						<?php endforeach; ?>
					</ul>
					<?php endif; ?>
					<a class="dsp-cta" href="<?php echo esc_url( dspc_quote_url() ); ?>">
						<span><?php echo esc_html( dspc_get( 'cta_text' ) ); ?></span>
						<?php echo $arrow; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</a>
				</div>
			</aside>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

/* ------------------------------------------------------------------
 * Shortcode 2: summary on the quote page  [devscripty_quote_summary]
 * Reads ?dsp_p=1&dsp_a=0-2&dsp_s=1 and recalculates the price on the server.
 * ---------------------------------------------------------------- */
function dspc_query_param( $key, $pattern ) {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! isset( $_GET[ $key ] ) || ! is_string( $_GET[ $key ] ) ) {
		return null;
	}
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$val = trim( wp_unslash( $_GET[ $key ] ) );
	return preg_match( $pattern, $val ) ? $val : null;
}

add_shortcode( 'devscripty_quote_summary', 'dspc_quote_shortcode' );

function dspc_quote_shortcode() {
	$platforms = dspc_parse( dspc_get( 'platforms' ) );
	$addons    = dspc_parse( dspc_get( 'addons' ) );
	$speeds    = dspc_parse( dspc_get( 'speeds' ) );

	$p = dspc_query_param( 'dsp_p', '/^\d{1,2}$/' );
	if ( empty( $platforms ) || null === $p || ! isset( $platforms[ (int) $p ] ) ) {
		if ( current_user_can( 'manage_options' ) ) {
			return '<p class="dsp-admin-note">[devscripty_quote_summary]: the summary appears after visiting this page from the configurator (the "' . esc_html( dspc_get( 'cta_text' ) ) . '" button). Visible to administrators only.</p>';
		}
		return '';
	}
	$pi = (int) $p;

	$ai  = array();
	$raw = dspc_query_param( 'dsp_a', '/^\d{1,2}(-\d{1,2})*$/' );
	if ( null !== $raw ) {
		foreach ( array_unique( array_map( 'intval', explode( '-', $raw ) ) ) as $i ) {
			if ( isset( $addons[ $i ] ) ) {
				$ai[] = $i;
			}
		}
	}

	$si  = dspc_default_index( $speeds );
	$raw = dspc_query_param( 'dsp_s', '/^\d{1,2}$/' );
	if ( null !== $raw && isset( $speeds[ (int) $raw ] ) ) {
		$si = (int) $raw;
	}

	$base  = $platforms[ $pi ]['price'];
	$add   = 0;
	$names = array();
	foreach ( $ai as $i ) {
		$add     += $addons[ $i ]['price'];
		$names[] = $addons[ $i ]['label'];
	}
	$speed = isset( $speeds[ $si ] ) ? $speeds[ $si ]['price'] : 0;
	$total = max( 0, $base + $add + $speed );
	$low   = round( $total * ( 1 - (float) dspc_get( 'low_pct' ) / 100 ) );
	$high  = round( $total * ( 1 + (float) dspc_get( 'high_pct' ) / 100 ) );
	$range = dspc_money( $low ) . ' - ' . dspc_money( $high );

	$summary = implode(
		"\n",
		array(
			'Price estimate (DevScripty configurator)',
			'Platform: ' . $platforms[ $pi ]['label'],
			'Add-ons: ' . ( $names ? implode( ', ', $names ) : 'none' ),
			'Delivery: ' . ( isset( $speeds[ $si ] ) ? $speeds[ $si ]['label'] : '-' ),
			'Estimate: ' . $range,
		)
	);

	dspc_enqueue_style();
	wp_enqueue_script( 'dspc-prefill' );

	ob_start();
	?>
	<div class="dsp-cfg dsp-quote" data-summary="<?php echo esc_attr( $summary ); ?>">
		<span class="dsp-result-label"><?php esc_html_e( 'Your Selected Configuration', 'dspc' ); ?></span>
		<ul class="dsp-quote-list">
			<li>
				<span class="dsp-q-name"><?php esc_html_e( 'Platform', 'dspc' ); ?></span>
				<strong><?php echo esc_html( $platforms[ $pi ]['label'] ); ?></strong>
				<em><?php echo esc_html( dspc_money( $base ) ); ?></em>
			</li>
			<?php foreach ( $ai as $i ) : ?>
				<li>
					<span class="dsp-q-name"><?php esc_html_e( 'Add-on', 'dspc' ); ?></span>
					<strong><?php echo esc_html( $addons[ $i ]['label'] ); ?></strong>
					<em>+<?php echo esc_html( dspc_money( $addons[ $i ]['price'] ) ); ?></em>
				</li>
			<?php endforeach; ?>
			<?php if ( isset( $speeds[ $si ] ) ) : ?>
				<li>
					<span class="dsp-q-name"><?php esc_html_e( 'Delivery', 'dspc' ); ?></span>
					<strong><?php echo esc_html( $speeds[ $si ]['label'] ); ?></strong>
					<em><?php echo esc_html( dspc_signed_money( $speed ) ); ?></em>
				</li>
			<?php endif; ?>
		</ul>
		<div class="dsp-quote-total">
			<span><?php echo esc_html( dspc_get( 'title' ) ); ?></span>
			<strong><?php echo esc_html( $range ); ?></strong>
		</div>
		<a class="dsp-quote-edit" href="<?php echo esc_url( dspc_configurator_url() ); ?>"><?php esc_html_e( 'Change selection', 'dspc' ); ?></a>
	</div>
	<?php
	return ob_get_clean();
}

/* ------------------------------------------------------------------
 * Settings page: Settings > Price Configurator
 * ---------------------------------------------------------------- */
add_action( 'admin_menu', function () {
	add_options_page( 'Price Configurator', 'Price Configurator', 'manage_options', 'dspc', 'dspc_settings_page' );
} );

add_action( 'admin_init', function () {
	register_setting( 'dspc_group', 'dspc_options', array( 'sanitize_callback' => 'dspc_sanitize' ) );
} );

function dspc_sanitize( $in ) {
	$out = array();
	foreach ( array( 'currency', 'currency_code', 'cta_text', 'title' ) as $k ) {
		$out[ $k ] = isset( $in[ $k ] ) ? sanitize_text_field( $in[ $k ] ) : '';
	}
	$out['low_pct']    = isset( $in['low_pct'] ) ? min( 90, absint( $in['low_pct'] ) ) : 5;
	$out['high_pct']   = isset( $in['high_pct'] ) ? min( 300, absint( $in['high_pct'] ) ) : 15;
	$out['load_fonts'] = empty( $in['load_fonts'] ) ? 0 : 1;
	foreach ( array( 'note', 'features', 'platforms', 'addons', 'speeds' ) as $k ) {
		$out[ $k ] = isset( $in[ $k ] ) ? sanitize_textarea_field( $in[ $k ] ) : '';
	}
	return $out;
}

function dspc_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$f = function ( $k ) {
		return esc_attr( dspc_get( $k ) );
	};
	?>
	<div class="wrap">
		<h1>DevScripty Price Configurator</h1>
		<p>Configurator: <code>[devscripty_configurator]</code> &nbsp;|&nbsp; Summary on the quote page: <code>[devscripty_quote_summary]</code></p>
		<p>The button links to: <code><?php echo esc_html( dspc_quote_url() ); ?></code> (calculated automatically from the site address).</p>
		<p class="description">To restore a default value, clear the field and save.</p>
		<form method="post" action="options.php">
			<?php settings_fields( 'dspc_group' ); ?>
			<table class="form-table" role="presentation">
				<tr><th>Currency</th><td>symbol <input type="text" class="small-text" name="dspc_options[currency]" value="<?php echo $f( 'currency' ); ?>"> &nbsp; code next to the price <input type="text" class="small-text" name="dspc_options[currency_code]" value="<?php echo $f( 'currency_code' ); ?>"></td></tr>
				<tr><th>Price range</th><td>
					from -<input type="number" min="0" max="90" class="small-text" name="dspc_options[low_pct]" value="<?php echo $f( 'low_pct' ); ?>">%
					to +<input type="number" min="0" max="300" class="small-text" name="dspc_options[high_pct]" value="<?php echo $f( 'high_pct' ); ?>">%</td></tr>
				<tr><th>Result title</th><td><input type="text" class="regular-text" name="dspc_options[title]" value="<?php echo $f( 'title' ); ?>"></td></tr>
				<tr><th>Text under the price</th><td><textarea class="large-text" rows="3" name="dspc_options[note]"><?php echo esc_textarea( dspc_get( 'note' ) ); ?></textarea></td></tr>
				<tr><th>Checklist items</th><td><textarea class="large-text" rows="3" name="dspc_options[features]"><?php echo esc_textarea( dspc_get( 'features' ) ); ?></textarea>
					<p class="description">One line = one item.</p></td></tr>
				<tr><th>Button text</th><td><input type="text" class="regular-text" name="dspc_options[cta_text]" value="<?php echo $f( 'cta_text' ); ?>"></td></tr>
				<tr><th>Fonts</th><td><label><input type="checkbox" name="dspc_options[load_fonts]" value="1" <?php checked( (int) dspc_get( 'load_fonts' ), 1 ); ?>> Load Playfair Display and Plus Jakarta Sans from Google Fonts</label></td></tr>
				<tr><th>Platforms</th><td><textarea class="large-text code" rows="5" name="dspc_options[platforms]"><?php echo esc_textarea( dspc_get( 'platforms' ) ); ?></textarea>
					<p class="description"><code>Name|Price|Icon</code>. Icons: wordpress, shopify, rocket, network, code, star (empty = picked automatically by name). An asterisk after the name = selected by default.</p></td></tr>
				<tr><th>Add-ons</th><td><textarea class="large-text code" rows="6" name="dspc_options[addons]"><?php echo esc_textarea( dspc_get( 'addons' ) ); ?></textarea>
					<p class="description"><code>Name|Price|Description</code></p></td></tr>
				<tr><th>Delivery speed</th><td><textarea class="large-text code" rows="4" name="dspc_options[speeds]"><?php echo esc_textarea( dspc_get( 'speeds' ) ); ?></textarea>
					<p class="description"><code>Name|Surcharge|Short label under the slider</code>. At least 2 lines are required. Line order matters: the quote link stores option numbers.</p></td></tr>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}
