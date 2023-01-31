# Composer WordPress Autoloader

[![Latest Version on Packagist](https://img.shields.io/packagist/v/quadlayers/wp-autoload.svg?style=flat-square)](https://packagist.org/packages/quadlayers/wp-autoload)

This composer plugin allows you to autoload WordPress files via Composer based on the [Wordpress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/)

This package is intended to be used as a dev package. It will help you in the development stage, avoiding the need to run the dump-autoload command every time a file is included in the repo.

This composer plugin should be used in conjunction with the classmap autolad. This will be used to create the optimized autoload in production. 

The use of classmap will prevent any validation issues in packagist that using unrecognized autoload creates.

In brief, the idea is to use the autolad of the plugin in development and the classmap in production.

## Installation
You can install the package via composer:

```bash
composer require quadlayers/wp-autoload --dev
```

## Usage
```json
{
	"config": {
		"allow-plugins": {
			"quadlayers/wp-autoload": true
		}
	},
	"autoload": {
		"classmap": [
			"src/",
			"lib/"
		]
	},
	"autoload-dev": {
		"exclude-from-classmap": [
			"src/",
			"lib/"
		]
	},
	"extra": {
		"quadlayers/wp-autoload": {
			"My_Plugin_Namespace\\": "src/",
			"My_Plugin_Namespace_2\\": "lib/"
		}
	}
}
```
## Development
Run the `composer install` command to install all dev dependencies. All classes will be loaded via quadlayers/wp-autoload package.

Use the `composer dump-autoload` or `composer dump-autoload -o` commands. This will use the autoload-dev settings to exclude classmap autoload because all files will be loaded via quadlayers/wp-autoload package.

## Production
Run the `composer install --no-dev` command to remove the dev dependencies. The quadlayers/wp-autoload wont be installed and all your files will be loaded with the classmap composer autoload.

You can also run the `composer dump-autoload --no-dev` or `composer dump-autoload -o --no-dev` commands, to exclude the classmap autoload.

## Todo
Create optimized files and remove the need to use autoload classmap.

## Credits
This plugin have been based on [Jetpack Autoloader](https://github.com/Automattic/jetpack-autoloader) and [Composer WordPress Autoloader
](https://github.com/alleyinteractive/composer-wordpress-autoloader).

## Contribute
Thats it! develop with WordPress naming conventions, run autoload command, and forget about us... I'm kidding; smush the stars button and contribute with this project. :D
