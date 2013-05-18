<?php

require_once(__DIR__ . '/../TaoBaoClient.php');

class TaoBaoClientTest extends PHPUnit_Framework_TestCase {

  public function testGetProductById() {
    $product = daigou\TaoBaoClient::getProductById(23407312096)->{'item'};
    $this->assertEquals('499.00', $product->{'price'});
    $this->assertEquals(23407312096, $product->{'num_iid'});
  }
}
