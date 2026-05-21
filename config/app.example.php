<?php

/**
 * Flash Example Configuration
 *
 * Merge the keys below into your application's config/app.php (or
 * config/app_local.php) — do not replace the whole file, since this snippet
 * only contains this plugin's configuration. When copying entries that
 * reference imported classes, use fully-qualified class names or move the
 * `use` imports to the top of the target file. Customize the values as needed.
 *
 * IMPORTANT: The `TransientFlash` namespace is NOT a user-set configuration namespace.
 * It is managed at runtime by Flash\Controller\Component\FlashComponent and
 * Flash\View\Helper\FlashHelper to stash transient (single-request, non-session) flash
 * messages. Entries live under `TransientFlash.<key>` (e.g. `TransientFlash.flash`) where
 * `<key>` is the flash key, and each is an array of message arrays
 * (type, message, key, element, params). The component writes them via Configure::write()
 * and the helper reads/merges them at render time and deletes them after output. You do
 * not pre-seed these — they are populated by transientSuccess()/transientError()/etc.
 *
 * What you DO configure is the component and helper themselves (via component/helper load
 * options, not Configure). The options below mirror their `_defaultConfig` for reference.
 */
return [
	// The line below is intentionally NOT a real config you set — it documents the runtime
	// storage namespace only. Leaving it empty is the correct/expected state.
	'TransientFlash' => [
		// Populated at runtime, keyed by flash key, e.g.:
		// 'flash' => [
		//     ['type' => 'success', 'message' => '...', 'key' => 'flash',
		//      'element' => 'flash/success', 'params' => []],
		// ],
	],

	// ----------------------------------------------------------------------------------
	// For reference only: FlashComponent options (load via $this->loadComponent('Flash.Flash', [...])).
	// These are the component's `_defaultConfig` defaults, not read from Configure:
	//   'key'              => 'flash',     // Session key
	//   'element'          => 'default',   // Default flash element
	//   'params'           => [],          // Default params passed to the element
	//   'clear'            => false,       // Clear existing messages for the key before adding
	//   'duplicate'        => true,        // Allow duplicate messages
	//   'limit'            => 10,          // Max messages per key (first in, first out)
	//   'headerKey'        => 'X-Flash',   // Response header for AJAX (empty string disables)
	//   'headerOnRedirect' => false,       // Inject the flash header on redirects
	//   'noSessionOnAjax'  => true,        // Route normal flash() calls to transient on AJAX
	//   'ajaxDetectors'    => ['ajax'],    // Request detectors used to identify AJAX
	//
	// FlashHelper options (load via setHelpers(['Flash.Flash' => [...]])).
	// These are the helper's `_defaultConfig` defaults, not read from Configure:
	//   'key'     => 'flash',
	//   'element' => 'default',
	//   'params'  => [],
	//   'order'   => ['error', 'warning', 'success', 'info'], // Render order by type
	//   'limit'   => 10,                                       // Max messages per type
	// ----------------------------------------------------------------------------------
];
