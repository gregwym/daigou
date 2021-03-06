<?php
namespace daigou;

class ExchangeRateManager {
	// In case of error
	const DEFAULT_CAD_TO_RMB = 6.0;
	const PATTERN = '/rhs:.*(?P<rate>\d+\.\d+)/';
	const DAY_IN_SECONDS = 86400;

	public static function get_rate_from_cad_to_rmb() {
		// Gets the exchange rate from the database first
		$rate_options = get_option('daigou-exchange-rate');
		$timestamp = time();
		if ($rate_options && $rate_options['expiry_time'] >= $timestamp) {
			return $rate_options['rate'];
		}

		$rate = self::DEFAULT_CAD_TO_RMB;
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_URL => 'http://openexchangerates.org/api/latest.json?app_id=fe7ede089a214e5cbf87f652e02129b7',
			CURLOPT_CONNECTTIMEOUT => 5,
		));

		$json = curl_exec($curl);
		if (!curl_errno($curl)) {
			$rates = json_decode($json);
			if ($rates && $rates->rates) {
				$usd_to_rmb = $rates->rates->CNY;
				$usd_to_cad = $rates->rates->CAD;
				$rate = $usd_to_rmb / $usd_to_cad;
			}
		}

		curl_close($curl);
		// Updates the exchange rate only if it's one day old or more
		$rate_options['rate'] = $rate;
		$rate_options['expiry_time'] = $timestamp + self::DAY_IN_SECONDS;
		update_option('daigou-exchange-rate', $rate_options);

		return $rate;
	}
}
