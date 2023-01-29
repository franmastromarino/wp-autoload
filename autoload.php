<?php
/**
 * QuadLayers WP Autoload
 *
 * @package   quadlayers/wp-autoload
 * @author    QuadLayers
 * @link      https://github.com/quadlayers/wp-autoload
 * @copyright Copyright (c) 2023
 * @license   GPL-3.0
 */

 use QuadLayers\WP_Autoload\Autoloader;

 $vendorDir      = dirname( __DIR__ );
 $baseDir        = realpath(dirname( $vendorDir . '../../../../' ));
 $namespaceCache = __DIR__ . '/namespace.php';

if ( ! file_exists( $namespaceCache ) ) {
	return;
}

$namespaces = include __DIR__ . '/namespace.php';

if ( empty( $namespaces ) ) {
	return;
}

if ( ! is_array( $namespaces ) ) {
	return;
}

foreach ( $namespaces as $namespace => $folder ) {
	new Autoloader( $namespace, $baseDir . '/' . $folder );
}
