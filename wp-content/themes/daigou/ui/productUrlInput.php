<?php
namespace daigou;

require_once(__DIR__ . '/../lib/Dom.php');

/**
 * A input box that allows users to paste in a Tao Bao product URL
 */
function productUrlInput() {
	$id = Dom::getId();
	echo "<div id=\"$id\"></div>";
}
