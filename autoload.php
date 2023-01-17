<?php
/**
 * QuadLayers WP Autoload
 *
 * @package   quadlayers/wp-autoload
 * @author    QuadLayers
 * @link      https://github.com/quadlayers/wp-autoload
 * @copyright Copyright (c) 2023
 * @license   GPL-2.0+
 */

 /* TODO: add credits to https://github.com/quadlayers/wpautoload */

use QuadLayers\WP_Autoloader\Autoloader;

$dir = __DIR__ . '../../../../';

$jsonContent = file_get_contents( $dir . '/composer.json' );
$composer = json_decode( $jsonContent, true );

if ( empty( $composer['extra']['quadlayers/autoload'] ) ) {
	return;
}

foreach ( $composer['extra']['quadlayers/autoload'] as $prefix => $folder ) {

	/* TODO: validar con classmap, tiene que existir folder */
	new Autoloader( $prefix, $dir . $folder );
}
