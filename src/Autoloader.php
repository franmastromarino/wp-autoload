<?php
/**
 * Autoload classes, interfaces and traits for your namespaces.
 *
 * @package   quadlayers/wp-autoload
 * @author    QuadLayers
 * @link      https://github.com/quadlayers/wp-autoload/
 * @copyright Copyright (c) 2023
 * @license   GPL-2.0+
 */

namespace QuadLayers\WP_Autoloader;

/**
 * @codeCoverageIgnore
 */
if ( class_exists( '\QuadLayers\WP_Autoloader\Autoload' ) ) {
	return;
}
/**
 * @codingStandardsIgnoreEnd
 */

/**
 * Class Autoload
 *
 * @package quadlayers/wp-autoload
 */
class Autoloader {

	/**
	 * Prefix for your namespace
	 *
	 * @var string
	 */
	private $prefix;
	/**
	 * Path to folder
	 *
	 * @var string
	 */
	private $folder;
	/**
	 * Cache
	 *
	 * @var Cache
	 */
	// private $cache;

	/**
	 * Autoload constructor.
	 *
	 * @param string $prefix Prefix for your namespace.
	 * @param string $folder Path to folder.
	 * @param Cache  $cache  Cache.
	 */
	public function __construct( $prefix, $folder, Cache $cache ) {
		$this->prefix = ltrim( $prefix, '\\' );
		$this->folder = $folder;
		// $this->cache  = $cache;
		spl_autoload_register( [ $this, 'autoload' ] );
	}

	/**
	 * Autoload files for custom plugins
	 *
	 * @param string $class Full class name.
	 *
	 * @throws Exception Class not found.
	 */
	public function autoload( $class ) {
		if ( 0 !== strpos( $class, $this->prefix ) ) {
			return;
		}

		// $path = $this->cache->get( $class );
		// if ( $path ) {
		// 	require_once $path;

		// 	return;
		// }

		$path = $this->file_path( $class );

		require_once $path;

		// $this->cache->update( $class, $path );
	}

	/**
	 * Find file path by namespace
	 *
	 * @param string $class Full class name.
	 *
	 * @return string
	 *
	 * @throws Exception Class not found.
	 */
	private function file_path( $class ) {
		$class        = str_replace( $this->prefix, '', $class );
		$plugin_parts = explode( '\\', $class );
		$name         = array_pop( $plugin_parts );
		$name         = preg_match( '/^(Interface|Trait)/', $name )
			? $name . '.php'
			: 'class-' . $name . '.php';
		$local_path   = implode( '/', $plugin_parts ) . '/' . $name;
		$local_path   = strtolower( str_replace( [ '\\', '_' ], [ '/', '-' ], $local_path ) );

		$path = $this->folder . '/' . $local_path;
		if ( file_exists( $path ) ) {
			return $path;
		}

		// $this->cache->clear_garbage();
		// throw new Exception( $class, $path );

		__doing_it_wrong( __METHOD__, sprintf('Class %s not found. File path not found %s', $class, $path) );
	}

}