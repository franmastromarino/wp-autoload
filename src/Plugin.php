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

// phpcs:disable PHPCompatibility.Keywords.NewKeywords.t_useFound
// phpcs:disable PHPCompatibility.LanguageConstructs.NewLanguageConstructs.t_ns_separatorFound
// phpcs:disable PHPCompatibility.Keywords.NewKeywords.t_namespaceFound

namespace QuadLayers\WP_Autoload;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;

/**
 * Class Plugin.
 *
 * @package quadlayers/wp-autoloader
 */
class Plugin implements PluginInterface, EventSubscriberInterface {

	/**
	 * IO object.
	 *
	 * @var IOInterface IO object.
	 */
	private $io;

	/**
	 * Composer object.
	 *
	 * @var Composer Composer object.
	 */
	private $composer;

	/**
	 * Do nothing.
	 *
	 * @param Composer    $composer Composer object.
	 * @param IOInterface $io IO object.
	 */
	public function activate( Composer $composer, IOInterface $io ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		$this->composer = $composer;
		$this->io       = $io;
	}

	/**
	 * Do nothing.
	 * phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
	 *
	 * @param Composer    $composer Composer object.
	 * @param IOInterface $io IO object.
	 */
	public function deactivate( Composer $composer, IOInterface $io ) {
		/*
		 * Intentionally left empty. This is a PluginInterface method.
		 * phpcs:enable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		 */
	}

	/**
	 * Do nothing.
	 * phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
	 *
	 * @param Composer    $composer Composer object.
	 * @param IOInterface $io IO object.
	 */
	public function uninstall( Composer $composer, IOInterface $io ) {
		/*
		 * Intentionally left empty. This is a PluginInterface method.
		 * phpcs:enable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		 */
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
		// When the autoloader is not required by the root package we don't want to execute it.
		// This prevents unwanted transitive execution that generates unused autoloaders or
		// at worst throws fatal executions.
		if ( ! $this->isRequiredByRoot() ) {
			return;
		}

		$config       = $this->composer->getConfig();
		$extra        = $this->composer->getPackage()->getExtra();
		$autoload     = $this->composer->getPackage()->getAutoload();
		$vendorDir    = $config->get( 'vendor-dir' );
		$vendorFolder = $config->raw()['config']['vendor-dir'];

		/**
		 * Validate the vendor directory.
		 */
		if ( 'vendor' !== $vendorFolder ) {
			$this->io->writeError( "\n<error>An error occurred while generating the autoloader files:", true );
			$this->io->writeError( 'The project\'s composer.json or composer environment set a non-default vendor directory.', true );
			$this->io->writeError( 'The default composer vendor directory must be used.</error>', true );
			exit();
		}

		/**
		 * Validate the autoload configuration.
		 */
		if ( ! isset( $autoload['classmap'] ) ) {
			$this->io->writeError( "\n<error>An error occurred while generating the autoloader files:", true );
			$this->io->writeError( 'The "classmap" autoload is required to generate optimized autoload.</error>', true );
			exit();
		}

		if ( ! is_array( $autoload['classmap'] ) || empty( $autoload['classmap'] ) ) {
			$this->io->writeError( "\n<error>An error occurred while generating the autoloader files:", true );
			$this->io->writeError( 'The "classmap" autoload must be a valid array with folder.</error>', true );
			exit();
		}

		/**
		 * Validate the extra configuration.
		 */
		if ( ! isset( $extra['quadlayers/wp-autoload'] ) ) {
			$this->io->writeError( "\n<error>An error occurred while generating the autoloader files:", true );
			$this->io->writeError( 'The "quadlayers/wp-autoload" must be defined.</error>', true );
			exit();
		}

		if ( ! is_array( $extra['quadlayers/wp-autoload'] ) || empty( $extra['quadlayers/wp-autoload'] ) ) {
			$this->io->writeError( "\n<error>An error occurred while generating the autoloader files:", true );
			$this->io->writeError( 'The "quadlayers/wp-autoload" must be a valid object with namespace and folder.</error>', true );
			exit();
		}

		/**
		 * Get root folder.
		 */
		$rootDir = str_replace( $vendorFolder, '', $vendorDir );

		foreach ( $extra['quadlayers/wp-autoload'] as $prefix => $folder ) {
			$folderPath = $rootDir . '/' . $folder;
			if ( is_string( $folderPath ) ) {
				if ( ! is_dir( $folderPath ) && strpos( $folderPath, '*' ) === false ) {
					$this->io->writeError( "\n<error>An error occurred while generating the autoloader files:", true );
					$this->io->writeError( sprintf( 'Could not scan for classes inside "%s" which does not appear to be a file nor a folder.</error>', $folder ), true );
					exit();
				}
				if ( ! in_array( $folder, $autoload['classmap'] ) ) {
					$this->io->writeError( "\n<error>An error occurred while generating the autoloader files:", true );
					$this->io->writeError( sprintf( 'The "%s" folder is not defined in the "classmap" autoload.</error>', $folder ), true );
					exit();
				}
			}
		}

		$namespaceCache = new NamespaceCache( $extra['quadlayers/wp-autoload'] );

		$namespaceCache->create();

		/*
		TODO: Generate the optimized autoloader files
		$generator = new AutoloadGenerator( $this->io );
		$generator->dump( $this->composer, $config, $localRepo, $package, $installationManager, 'composer', $optimize, $suffix );
		*/
	}

	/**
	 * Checks to see whether or not the root package is the one that required the autoloader.
	 *
	 * @return bool
	 */
	private function isRequiredByRoot() {
		$package  = $this->composer->getPackage();
	
		$devRequires = $package->getDevRequires();

		if ( ! is_array( $devRequires ) ) {
			$devRequires = array();
		}

		if ( empty( $devRequires ) ) {
			$this->io->writeError( "\n<error>The package should be required in dev.</error>", true );
			exit();
		}

		foreach ( $devRequires as $require ) {
			if ( 'quadlayers/wp-autoload' === $require->getTarget() ) {
				return true;
			}
		}

		return false;
	}
}