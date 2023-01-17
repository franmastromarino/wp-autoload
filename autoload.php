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

use Composer\Factory;
// use QuadLayers\WP_Autoloader\Cache;
use Composer\Json\JsonFile;
use QuadLayers\WP_Autoloader\Autoloader;

$file    = new JsonFile( Factory::getComposerFile() );
$content = $file->read();
if ( empty( $content['extra']['quadlayers/wp-autoload'] ) ) {
	return;
}

$dir   = dirname( Factory::getComposerFile() ) . '/';
// $cache = new Cache();

foreach ( $content['extra']['quadlayers/wp-autoload'] as $prefix => $folder ) {

	/* TODO: validar con classmap, tiene que existir folder */

	new Autoloader( $prefix, $dir . $folder, $cache );
}
