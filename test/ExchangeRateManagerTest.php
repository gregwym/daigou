<?php

require_once(__DIR__ . '/../src/lib/ExchangeRateManager.php');

use daigou\ExchangeRateManager as ExchangeRateManager;

class ExchangeRateManagerTest extends PHPUnit_Framework_TestCase {

	public function test_get_rate_from_cad_to_rmb() {
		$rate = ExchangeRateManager::get_rate_from_cad_to_rmb();
		$this->assertTrue(is_numeric($rate));
	}
}