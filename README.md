# DevScripty Price Configurator

A lightweight WordPress plugin that adds an interactive project cost estimator to any page. Built for Breakdance, but it works with any builder that can output a shortcode.

The calculation runs entirely in the browser. The plugin makes no AJAX or REST requests, so it does not trigger 403 errors from WAF layers such as ModSecurity or Cloudflare.

## Features

- Platform cards with icons, add-on checkboxes and a delivery-speed slider
- Live price range (configurable lower and upper percentage)
- "Submit Proposal Request" button that links to a quote page with the selected options
- Summary shortcode for the quote page, recalculated on the server from the option numbers in the URL
- Optional auto-fill of a form field with the selected configuration
- No hardcoded domain: safe to move from staging to production
- All prices, labels and texts are editable in the settings page
- No build step. Platform icons use Font Awesome (already loaded on most sites; otherwise enable the option in settings)

## Requirements

- WordPress 5.0.3 or newer
- PHP 7.0 or newer

## Installation

1. Download the latest release zip (or zip the plugin folder).
2. In WordPress go to Plugins > Add New > Upload Plugin and activate it.
3. If the upload returns a 403 error, upload the unzipped folder to `wp-content/plugins/` over FTP or a file manager instead.

## Usage

### Configurator

Add this shortcode to a page (in Breakdance use the Shortcode element):

```
[devscripty_configurator]
```

The plugin outputs only the configurator. Section background, headings and spacing come from your page builder.

### Quote page

1. Create a page with the slug `request-a-quote` (URL: `/request-a-quote/`).
2. Place `[devscripty_quote_summary]` above your request form.
3. Give the form's message field the CSS class `dsp-prefill` to fill it with the selected configuration automatically.

The summary is shown only when the visitor arrives from the configurator. Administrators see a short hint otherwise.

## Settings

Settings > Price Configurator. One option per line:

| Field | Format |
| --- | --- |
| Platforms | `Name\|Price\|Icon` (`*` after the name = selected by default) |
| Add-ons | `Name\|Price\|Description` |
| Delivery speed | `Name\|Surcharge\|Short label under the slider` (at least 2 lines) |

Available icons (Font Awesome): `wordpress`, `shopify`, `rocket`, `network`, `code`, `star`. If the icon is empty it is picked by the platform name.

The order of lines matters: the quote link stores option numbers, not names.

## Filters

```php
// Change the quote page URL (default: home_url( '/request-a-quote/' )).
add_filter( 'dspc_quote_url', function ( $url ) {
	return home_url( '/get-a-quote/' );
} );

// Change the "Change selection" link on the quote page (default: home_url( '/#estimator' )).
add_filter( 'dspc_configurator_url', function ( $url ) {
	return home_url( '/pricing/#calculator' );
} );
```

## Moving from staging to production

The plugin stores no URLs in the database, only prices and texts. Nothing needs to be replaced after the move.

## Changelog

### 1.2.2
- Platform icons switched to Font Awesome
- New setting: load Font Awesome 6.4.0 from cdnjs

### 1.2.1
- Output only the configurator (no section heading or outer card)

### 1.2.0
- New design: platform icons, checkbox add-ons, delivery slider, result card with checklist
- Button links to the quote page with the selected options

### 1.1.0
- Quote page summary shortcode and form auto-fill

### 1.0.0
- Initial release

## License

GPL-2.0-or-later
