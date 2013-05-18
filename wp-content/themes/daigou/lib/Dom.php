<?php
namespace daigou;

class Dom {

	const PREFIX = 'daigou-';

	private static $id = 0;

	/**
	 * Generates a unique ID
	 */
	public static function getId() {
		return self::PREFIX . self::$id++;
	}
}