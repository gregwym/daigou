# daigouge.com

This WordPress has been customized for daigouge.ca. It integrated WooCommerce plugin and Mystile theme. 

## Requirements
- Install [Node.js](http://nodejs.org/)
- Install [Grunt](http://gruntjs.com/)
- Install [Compass](http://compass-style.org/)

## Setup
- `npm install`
- Add `src/wp-config.dev.php`
- Add `src/wp-config.stage.php`

## Build
- `grunt build --target=dev` to trigger development build
- `grunt build --target=staging` to trigger staging build
- `grunt build --target=prod` to trigger production build

## Debug
	grunt build --target=dev
	cd target/dev
	php -S localhost:8000

## Auto build (Modified files will be built automatically)
	grunt auto-build --target=dev

