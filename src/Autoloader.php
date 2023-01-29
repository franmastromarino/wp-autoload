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

use QuadLayers\WP_Autoload\Exception;

/**
 * Class Autoloader
 *
 * @package quadlayers/wp-autoload
 */
class Autoloader {

	/**
	 * Prefix for your namespace
	 *
	 * @var string
	 */
	private $namespace;
	/**
	 * Path to folder
	 *
	 * @var string
	 */
	private $folder;

	/**
	 * Autoloader constructor.
	 *
	 * @param string $namespace Prefix for your namespace.
	 * @param string $folder Path to folder.
	 */
	public function __construct( $namespace, $folder ) {
		$this->namespace = ltrim( $namespace, '\\' );
		$this->folder    = $folder;

		spl_autoload_register( array( $this, 'autoload' ) );
	}

	/**
	 * Autoloader files for custom plugins
	 *
	 * @param string $class Full class name.
	 *
	 * @throws Exception Class not found.
	 */
	public function autoload( $class ) {
		if ( 0 !== strpos( $class, $this->namespace ) ) {
			return;
		}

		$path = $this->file_path( $class );

		require_once $path;
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
		$class        = str_replace( $this->namespace, '', $class );
		$plugin_parts = explode( '\\', $class );
		$name         = array_pop( $plugin_parts );
		$name         = preg_match( '/^(Interface|Trait)/', $name )
			? $name . '.php'
			: 'class-' . $name . '.php';
		$local_path   = implode( '/', $plugin_parts ) . '/' . $name;
		$local_path   = strtolower( str_replace( array( '\\', '_' ), array( '/', '-' ), $local_path ) );

		$path = $this->folder . '/' . $local_path;
		if ( file_exists( $path ) ) {
			return $path;
		}

		throw new Exception( __METHOD__, sprintf( 'Class %s not found. File path not found %s', $class, $path ) );
	}

}
