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
  * Class NamespaceCache
  *
  * @package quadlayers/wp-autoload
  */
class NamespaceCache {

	/**
	 * Namespaces array
	 *
	 * @var array
	 */
	private $namespaces = array();
	/**
	 * Classmap file
	 *
	 * @var string
	 */
	private $filePath;
	/**
	 * NamespaceCache constructor.
	 */
	public function __construct( array $namespaces ) {
		$this->namespaces = $namespaces;
		$this->filePath   = __DIR__ . '../../namespace.php';
	}

	/**
	 * Save namespace cache file.
	 */
	public function create() {
		$this->createFilePath();
		file_put_contents( $this->filePath, '<?php return [' . $this->createFileContent() . '];', LOCK_EX );
	}

	/**
	 * Delete namespace file.
	 *
	 * @return string
	 */
	public function delete() {
		if ( file_exists( $this->filePath ) ) {
			unlink( $this->filePath );
		}
	}

	/**
	 * Create cache directory.
	 */
	private function createFilePath() {
		if ( ! file_exists( dirname( $this->filePath ) ) ) {
			mkdir( dirname( $this->filePath ), 0755, true );
		}
	}

	/**
	 * Create namespace file content.
	 *
	 * @return string
	 */
	private function createFileContent() {
		$content = '';
		$last    = end( $this->namespaces );
		foreach ( $this->namespaces as $namespace => $folder ) {

			$namespace = json_encode( $namespace );

			$content .= $namespace . '=>' . '"' . $folder . '"';

			if ( $folder !== $last ) {
				$content .= ",\n";
			}
		}

		return $content;
	}

}
