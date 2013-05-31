# Diggos.ca

This WordPress app has been customized for Diggos.ca. It integrated WooCommerce plugin and Mystile theme. 

Before deploying, remember to update the `wp-taobao-url` plugin to the latest version by

	cd wp-taobao-url
	git pull

## Requirements
- Install [Node.js](http://nodejs.org/)
- Install [Grunt](http://gruntjs.com/)
- Install [Compass](http://compass-style.org/)

## Setup (You only need to run this once)
	npm install
	grunt setup

## Build
- `grunt build:dev` to build for development, JavaScript and CSS files will be unminified
- `grunt build:prod` to build for production, JavaScript and CSS files will be minified

## Run
	grunt build:dev
	cd target
	php -S localhost:8000
 