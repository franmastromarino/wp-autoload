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

use Composer\Composer;
use Composer\Autoload\AutoloadGenerator as ComposerAutoloadGenerator;
use Composer\IO\IOInterface;
use Composer\Util\Filesystem;

/**
 * Class FileAutoloadComposerUpdate.
 *
 * @package quadlayers/wp-autoloader
 */
class FileAutoloadComposerUpdate extends ComposerAutoloadGenerator {

	/**
	 * Composer object.
	 *
	 * @var Composer object.
	 */
	private $composer;

	/**
	 * IO object.
	 *
	 * @var IOInterface object.
	 */
	private $io;

	/**
	 * Filesystem object.
	 *
	 * @var FileSystem object.
	 */
	private $filesystem;

	/**
	 * FileAutoloadFilePackageCreate object.
	 *
	 * @var FileAutoloadFilePackageCreate object.
	 */
	private $autoloadPackage;

	/**
	 * Vendor path.
	 *
	 * @var string.
	 */
	private $vendorPath;

	/**
	 * Constructor.
	 *
	 * @param Composer                      $composer object.
	 * @param IOInterface                   $io object.
	 * @param Filesystem                    $filesystem object.
	 * @param FileAutoloadFilePackageCreate $autoloadPackage object.
	 */
	public function __construct( Composer $composer, IOInterface $io, Filesystem $filesystem, FileAutoloadFilePackageCreate $autoloadPackage ) {
		$this->composer        = $composer;
		$this->io              = $io;
		$this->filesystem      = $filesystem;
		$this->autoloadPackage = $autoloadPackage;
		/**
		 * Get the vendor directory.
		 */
		$vendorDir = $this->composer->getConfig()->get( 'vendor-dir' );
		/**
		 * Get the vendor path.
		 */
		$this->vendorPath = $this->filesystem->normalizePath( realpath( realpath( $vendorDir ) ) );
	}

	/**
	 * Updates the autoload file.
	 */
	public function update(): void {

		if ( ! $this->filesystem->filePutContentsIfModified( $this->getFilePath(), $this->getContent() ) ) {
			$this->io->writeError( "\n<error>QuadLayers WP Autoload error:", true );
			$this->io->write( 'Can\'t inject autoload into vendor/autoload.php.</error>' );
			exit();
		}

		$this->io->write(
			'<info>QuadLayers WP Autoload injected into vendor/autoload.php.</info>'
		);

	}

	/**
	 * Deletes the autoload file.
	 */
	public function delete(): void {
		if ( $this->filesystem->remove( $this->getFilePath() ) ) {
			$this->io->write( '<info>QuadLayers WP Autoload removed.</info>' );
		}
	}

	/**
	 * Create the autoloader file contents to write to vendor/wordpress-autoload.php.
	 *
	 * @return string
	 * @throws RuntimeException If the autoloader file could not be found.
	 */
	protected function getContent(): string {
		$filename   = basename($this->autoloadPackage->getFilePath());
		$autoloader = file_get_contents($this->composer->getConfig()->get('vendor-dir') . '/autoload.php');

		// Remove the opening PHP tag from the existing autoloader
		$autoloader = ltrim($autoloader, '<?php');

		$contents = preg_replace_callback(
			'/^return (.*);$/m',
			function ($matches) use ($filename) {
				$autoloader = <<<AUTOLOADER
	/*
	  QuadLayers WP Autoload injected by quadlayers/wp-autoload
	*/
	require_once __DIR__ . '/{$filename}';

	\$loader = {$matches[1]};

	return \$loader;
	AUTOLOADER;

				return "$autoloader\n";
			},
			$autoloader,
			1,
			$count
		);

		if (!$count) {
			throw new RuntimeException('Error finding proper place to inject autoloader.');
		}

		// Prepend the opening PHP tag to the modified autoloader
		$contents = "<?php\n\n" . $contents;

		return $contents;
	}


	/**
	 * Retrieve the file path for the autoloader file.
	 *
	 * @return string
	 */
	protected function getFilePath(): string {
		return "{$this->vendorPath}/autoload.php";
	}


}
