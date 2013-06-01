<?php
namespace daigou;

class ExchangeRateManager {
	// In case of error
	const DEFAULT_CAD_TO_RMB = 6.0;

	public static function get_rate_from_cad_to_rmb() {
		// TODO: cache the result
		$rate = self::DEFAULT_CAD_TO_RMB;
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_URL => 'http://rate-exchange.appspot.com/currency?from=CAD&to=RMB',
			CURLOPT_CONNECTTIMEOUT => 5,
		));

		$response = curl_exec($curl);
		if ($response) {
			$result = json_decode($response);
			$rate = (float) $result->{'rate'};
		}

		curl_close($curl);
		return $rate;
	}
}
