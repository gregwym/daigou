(function($, ProductUrlInput) {
	$(document).ready(function() {
		var urlInput = new ProductUrlInput();
		$('#taobao-add-product').append(urlInput.createDom());
		urlInput.onDomCreated();
	});
})(jQuery, window['daigou.ProductUrlInput']);
