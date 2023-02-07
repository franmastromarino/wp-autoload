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

use QuadLayers\WP_Autoload\AutoloaderException;

/**
 * Class Autoloader.
 *
 * @package quadlayers/wp-autoloader
 */
class Autoloader {
	/**
	 * Namespace to autoload.
	 *
	 * @var string
	 */
	protected string $namespace;

	/**
	 * Root path of the namespace to load from.
	 *
	 * @var string
	 */
	protected string $rootPath;

	/**
	 * Missing classes for the autoloader.
	 *
	 * @var bool[]
	 * @psalm-var array<string, bool>
	 */
	protected array $missingClasses = array();

	/**
	 * Generate an autoloader for the WordPress file naming conventions.
	 *
	 * @param string $namespace Namespace to autoload.
	 * @param string $rootPath Path in which to look for files.
	 * @return static Function for spl_autoload_register().
	 */
	public static function generate( string $namespace, string $rootPath ): callable {
		return new static( $namespace, $rootPath );
	}

	/**
	 * Constructor.
	 *
	 * @param string $namespace Namespace to register.
	 * @param string $rootPath Root path of the namespace.
	 */
	public function __construct( string $namespace, string $rootPath ) {
		$this->namespace = $namespace;
		/**
		 * Ensure consistent root.
		 */
		$this->rootPath = rtrim( $rootPath, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;
	}

	/**
	 * Check if a class was missing from the autoloader.
	 *
	 * @param string $className Class to check.
	 * @return bool
	 */
	
	private function isValidNamespace( string $className ): bool {

		/**
		 * Ensure the namespace ends with a backslash.
		 */
		$namespace = "$this->namespace\\";
		/**
		 * Ensure the namespace is at the beginning of the class name.
		 */
		if ( 0 !== \strpos( $className, $namespace ) ) {
			return false;
		}

		return true;

	}
	/**
	 * Check if a class was missing from the autoloader.
	 *
	 * @param string $className Class to check.
	 * @return bool
	 */
	private function isMissingClass( string $className ): bool {
		return isset( $this->missingClasses[ $className ] );
	}

	/**
	 * Register the autoloader.
	 */
	public function register() {
		spl_autoload_register( $this );
	}

	/**
	 * Unregister the autoloader.
	 */
	public function unregister() {
		spl_autoload_unregister( $this );
	}

	/**
	 * Invoke method of the class.
	 *
	 * @param string $className Class being autoloaded.
	 */
	public function __invoke( string $className ) {

		if ( ! $this->isValidNamespace( $className ) ) {
			return;
		}

		/**
		 * Check if the class was previously not found.
		 */
		if ( $this->isMissingClass( $className ) ) {
			return;
		}

		$classFilePath = $this->getClassFilePath( $className );

		if ( $classFilePath ) {
			require_once $classFilePath;
		} else {
			/**
			 * Mark the class as not found to save future lookups.
			 */
			$this->missingClasses[ $className ] = true;
		}
	}

	/**
	 * Find a file for the given class.
	 *
	 * @param string $className Class to find.
	 * @return string|null
	 */
	protected function getClassFilePath( string $className ): ?string {

		/**
		 * Break up the classname into parts.
		 */
		$parts = \explode( '\\', $className );

		/**
		 * Retrieve the class name (last item) and convert it to a filename.
		 */
		$class    = \strtolower( \str_replace( '_', '-', \array_pop( $parts ) ) );
		$basePath = '';

		/**
		 * Build the base path relative to the sub-namespace.
		 */
		$subNamespace = \substr( \implode( DIRECTORY_SEPARATOR, $parts ), \strlen( $this->namespace ) );

		if ( ! empty( $subNamespace ) ) {
			$basePath = \str_replace( '_', '-', \strtolower( $subNamespace ) );
		}

		/**
		 * Support multiple locations since the file could be a class, trait, interface or enum.
		 */
		$paths = array(
			'%1$s' . DIRECTORY_SEPARATOR . 'class-%2$s.php',
			'%1$s' . DIRECTORY_SEPARATOR . 'trait-%2$s.php',
			'%1$s' . DIRECTORY_SEPARATOR . 'interface-%2$s.php',
			'%1$s' . DIRECTORY_SEPARATOR . 'enum-%2$s.php',
		);

		/*
		* Attempt to find the file by looping through the various paths.
		*
		* Autoloading a class will also cause a trait or interface with the
		* same fully qualified name to be autoloaded, as it's impossible to
		* tell which was requested.
		*/
		foreach ( $paths as $path ) {
			$path = $this->rootPath . \sprintf( $path, $basePath, $class );

			if ( \file_exists( $path ) ) {
				return $path;
			}
		}

		throw new AutoloaderException( $className, $path );
	}
}