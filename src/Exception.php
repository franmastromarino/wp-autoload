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

/**
 * Class Exception
 *
 * @package quadlayers/wp-autoload
 */
class Exception extends \Exception {

	/**
	 * Exception constructor.
	 *
	 * @param string $class Class name.
	 * @param string $path  Expected path.
	 */
	public function __construct( $class, $path ) {
		$message = '<strong>QuadLayers/WP_Autoload/Exception</strong>: ';

		$message .= '<em>' . $class . '</em> is not found in <code>' . $path . '<code>';

		parent::__construct( $message );
	}

}
