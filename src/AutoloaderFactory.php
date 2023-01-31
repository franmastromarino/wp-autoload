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

namespace QuadLayers\WP_Autoload;

use QuadLayers\WP_Autoload\Autoloader;

/**
 * Class AutoloaderFactory.
 *
 * @package quadlayers/wp-autoloader
 */
class AutoloaderFactory {

	/**
	 * Generate an autoloader instances.
	 *
	 * @param array $rules Array of namespaces.
	 * @return array<Autoloader>
	 */
	public static function generateFromRules( array $rules ) {
		$loaders = [];
		foreach ( $rules as $namespace => $paths ) {
			foreach ( $paths as $path ) {
				$loaders[] = new Autoloader( $namespace, $path );
			}
		}
		return $loaders;
	}

	/**
	 * Register autoloaders from rules.
	 *
	 * @param array<string, string> $rules Array of rules.
	 * @return void
	 */
	public static function registerFromRules( array $rules ) {
		foreach ( static::generateFromRules( $rules ) as $autoloader ) {
			$autoloader->register();
		}
	}
}
