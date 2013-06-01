# Diggos.ca

This WordPress app has been customized for Diggos.ca. It integrated WooCommerce plugin and Mystile theme. 

Before deploying, remember to update the `wp-taobao-url` plugin to the latest version by

	cd wp-taobao-url
	git pull

## Requirements
- Install [Node.js](http://nodejs.org/)
- Install [Grunt](http://gruntjs.com/)
- Install [Compass](http://compass-style.org/)

## Setup
- `npm install`
- Add `config/wp-config.dev.php`
- Add `config/wp-config.stage.php`

## Run
	grunt build:dev
	cd target/dev
	php -S localhost:8000
