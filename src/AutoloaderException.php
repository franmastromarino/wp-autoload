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
 * Class AutoloaderException
 *
 * @package quadlayers/wp-autoload
 */
class AutoloaderException extends \Exception {

	/**
	 * Exception constructor.
	 *
	 * @param string $className Class name.
	 * @param string $path  Expected path.
	 */
	public function __construct( $className, $path ) {
		parent::__construct( '<em>' . $className . '</em> is not found in <code>' . $path . '<code>' );
	}

}
