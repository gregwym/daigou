<?php

use daigou\TaoBaoClient as TaoBaoClient;

require_once(__DIR__ . '/../src/lib/TaoBaoClient.php');

class TaoBaoClientTest extends PHPUnit_Framework_TestCase {

  public function testGetProductById() {
    $product = TaoBaoClient::getProductById(23407312096)->{'item'};
    $this->assertEquals('499.00', $product->{'price'});
    $this->assertEquals(23407312096, $product->{'num_iid'});
  }
}
