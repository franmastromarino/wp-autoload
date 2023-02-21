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
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use Composer\Util\Filesystem;
use QuadLayers\WP_Autoload\FileAutoloadFilePackageCreate;
use QuadLayers\WP_Autoload\FileAutoloadComposerUpdate;

/**
 * Class Plugin.
 *
 * @package quadlayers/wp-autoloader
 */
class Plugin implements PluginInterface, EventSubscriberInterface {

	/**
	 * IO object.
	 *
	 * @var IOInterface object.
	 */
	private $io;

	/**
	 * Composer object.
	 *
	 * @var Composer object.
	 */
	private $composer;

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
	 * FileAutoloadComposerUpdate object.
	 *
	 * @var FileAutoloadComposerUpdate object.
	 */
	private $autoloadComposer;

	/**
	 * Create package autoload and update composer autoload files.
	 *
	 * @param Composer    $composer Composer object.
	 * @param IOInterface $io IO object.
	 */
	public function activate( Composer $composer, IOInterface $io ) {
		$this->composer   = $composer;
		$this->io         = $io;
		$this->filesystem = new Filesystem();
		/**
		 * Instance package autoload creator.
		 */
		$this->autoloadPackage = new FileAutoloadFilePackageCreate(
			$this->composer,
			$this->io,
			$this->filesystem
		);
		/**
		 * Instance autoload FileAutoloadComposerUpdate.
		 */
		$this->autoloadComposer = new FileAutoloadComposerUpdate(
			$this->composer,
			$this->io,
			$this->filesystem,
			$this->autoloadPackage
		);
	}

	/**
	 * Do nothing.
	 *
	 * @param Composer    $composer Composer object.
	 * @param IOInterface $io IO object.
	 */
	public function deactivate( Composer $composer, IOInterface $io ) {
		/*
		 * Intentionally left empty. This is a PluginInterface method.
		 */
	}

	/**
	 * Delete package autoload file.
	 *
	 * @param Composer    $composer Composer object.
	 * @param IOInterface $io IO object.
	 */
	public function uninstall( Composer $composer, IOInterface $io ) {
		$this->autoloadPackage->delete();
	}

	/**
	 * Tell composer to listen for events and do something with them.
	 *
	 * @return array List of subscribed events.
	 */
	public static function getSubscribedEvents() {
		return array(
			ScriptEvents::POST_AUTOLOAD_DUMP => 'postAutoloadDump',
		);
	}

	/**
	 * Generate the custom autolaoder.
	 *
	 * @param Event $event Script event object.
	 */
	public function postAutoloadDump( Event $event ) {

		/**
		 * When the autoloader is not required by the root package we don't want to execute it.
		 * This prevents unwanted transitive execution that generates unused autoloaders or
		 * at worst throws fatal executions.
		 */
		if ( ! $this->isRequiredByRoot() ) {
			exit();
		}

		/**
		 * Check if the config is valid.
		 */
		if ( ! $this->isValidConfig() ) {
			exit();
		}

		/**
		 * Create the package autoloader.
		 */
		$this->autoloadPackage->create();

		/**
		 * Update the composer autoloader.
		 */
		$this->autoloadComposer->update();
	}

	/**
	 * Checks to see whether or not the root package is the one that required the autoloader.
	 *
	 * @return bool
	 */
	private function isRequiredByRoot() {
		$package = $this->composer->getPackage();

		$devRequires = $package->getDevRequires();

		if ( ! is_array( $devRequires ) ) {
			$devRequires = array();
		}

		if ( empty( $devRequires ) ) {
			$this->io->writeError( "\n<error>QuadLayers WP Autoload error:", true );
			$this->io->writeError( 'The package should be required in dev.</error>', true );
			return false;
		}

		foreach ( $devRequires as $require ) {
			if ( 'quadlayers/wp-autoload' === $require->getTarget() ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Checks to see whether or not the autoloader is configured correctly.
	 *
	 * @return bool
	 */
	private function isValidConfig() {

		$config       = $this->composer->getConfig();
		$extra        = $this->composer->getPackage()->getExtra();
		$autoload     = $this->composer->getPackage()->getAutoload();
		$vendorDir    = $config->get( 'vendor-dir' );
		$vendorFolder = $config->raw()['config']['vendor-dir'];

		/**
		 * Validate the vendor directory.
		 */
		if ( 'vendor' !== $vendorFolder ) {
			$this->io->writeError( "\n<error>QuadLayers WP Autoload error:", true );
			$this->io->writeError( 'The project\'s composer.json or composer environment set a non-default vendor directory.', true );
			$this->io->writeError( 'The default composer vendor directory must be used.</error>', true );
			return false;
		}

		/**
		 * Validate the autoload configuration.
		 */
		if ( empty( $autoload['classmap'] ) || ! is_array( $autoload['classmap'] ) ) {
			$this->io->writeError( "\n<error>QuadLayers WP Autoload error:", true );
			$this->io->writeError( 'The "classmap" autoload is required to generate optimized autoload.</error>', true );
			return false;
		}

		/**
		 * Validate the extra configuration.
		 */
		if ( ! isset( $extra['quadlayers/wp-autoload'] ) || ! is_array( $extra['quadlayers/wp-autoload'] ) ) {
			$this->io->writeError( "\n<error>QuadLayers WP Autoload error:", true );
			$this->io->writeError( 'The "quadlayers/wp-autoload" must be defined.</error>', true );
			return false;
		}

		return true;

	}

}
