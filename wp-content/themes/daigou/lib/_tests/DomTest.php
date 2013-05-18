<?php

use daigou\Dom as Dom;

require_once(__DIR__ . '/../Dom.php');

class TaoBaoClientTest extends PHPUnit_Framework_TestCase {

  public function testGetId() {
  	$id1 = Dom::getId();
  	$id2 = Dom::getId();
  	$id3 = Dom::getId();

  	$this->assertTrue($id1 != $id2);
  	$this->assertTrue($id1 != $id3);
  	$this->assertTrue($id2 != $id3);
  }
}
