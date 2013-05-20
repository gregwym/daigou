(function() {
	var daigou = window['daigou'];
	if (!daigou) {
		daigou = window['daigou'] = {};
	}

	var $ = jQuery;

	function ProductUrlInput(element) {
		this._element = element;
		this._render();
	}

	var prototype = ProductUrlInput.prototype;
	prototype._render = function() {

	};

	daigou.ProductUrlInput = ProductUrlInput;

})();
