<?php
namespace daigou;

require_once(__DIR__ . '/Configuration.php');
require_once(__DIR__ . '/taobao-sdk/TopSdk.php');

class TaoBaoClient {
	public static function getProductById($id) {
		$client = self::getClient();
		$request = new \ItemGetRequest();
		$request->setFields('
			title,
			num_iid,
			detail_url,
			item_weight,
			pic_url,
			price
		');
		$request->setNumIid($id);
		$response = $client->execute($request);
		return $response;
	}

	private static function getClient() {
		$client = new \TopClient();
		$client->appkey = Configuration::TAOBAO_API_KEY;
		$client->secretKey = Configuration::TAOBAO_API_SECRET;
		$client->format = 'json';
		return $client;
	}
}