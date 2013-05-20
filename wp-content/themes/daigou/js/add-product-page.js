(function($, ProductUrlInput) {
	$(document).ready(function() {
		var urlInput = new ProductUrlInput();
		$('#content').append(urlInput.createDom());
		urlInput.onDomCreated();
	});
})(jQuery, this['daigou.ProductUrlInput']);
