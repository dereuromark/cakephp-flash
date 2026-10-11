<?php

/**
 * Merge the Flash entry into your application's config/app.php or config/app_local.php.
 * Load options override these shared component and helper defaults.
 */
return [
	'Flash' => [
		'key' => 'flash',
		'limit' => 10,
		'params' => [],
		// Helper output order.
		'order' => ['error', 'warning', 'success', 'info'],
		// Component AJAX response options.
		'headerKey' => 'X-Flash',
		'headerOnRedirect' => false,
		'noSessionOnAjax' => true,
		'ajaxDetectors' => ['ajax'],
	],
];
