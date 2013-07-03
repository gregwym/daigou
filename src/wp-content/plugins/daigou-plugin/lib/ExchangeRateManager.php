<?php
namespace daigou;

class ExchangeRateManager {
	// In case of error
	const DEFAULT_CAD_TO_RMB = 6.0;
	const PATTERN = '/rhs:.*(?P<rate>\d+\.\d+)/';

	public static function get_rate_from_cad_to_rmb() {
		// TODO: cache the result
		$rate = self::DEFAULT_CAD_TO_RMB;
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_URL => 'http://www.google.com/ig/calculator?hl=en&q=1CAD=?RMB',
			CURLOPT_CONNECTTIMEOUT => 5,
		));

		$response = curl_exec($curl);
		if (!curl_errno($curl)) {
			preg_match(self::PATTERN, $response, $matches);
			if ($matches['rate']) {
				$rate = (float) $matches['rate'];
			}
		}

		curl_close($curl);
		return $rate;
	}
}
