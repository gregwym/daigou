(function($, Uri, Configuration, Dom, ProductDetailBox) {
	var attr = Dom.getAttributeString;
	var DEFAULT_URL_TEXT = 'Paste in a TaoBao URL';

	var ProductUrlInput = window['daigou.ProductUrlInput'] = function() {};

	var prototype = ProductUrlInput.prototype;
	prototype.createDom = function() {
		var id = this._id = Dom.getId();

		return ['\
			<div ', attr('id', id) , attr('class', 'daigou-product-url-input'), '> \
				<div class="url-input-container"> \
					<input name="url" type="text"', attr('value', DEFAULT_URL_TEXT), '/> \
					<button>Get the product</button> \
				</div> \
				<div class="product-detail-box-container"></div> \
			</div> \
		'].join('');
	};

	prototype.onDomCreated = function() {
		var id = '#' + this._id;
		var button = $(id + ' .url-input-container button');
		var urlInput = $(id + ' .url-input-container input');
		var me = this;

		urlInput.mouseover(function() {
			urlInput.select();
		});

		button.click(function() {
			var url = new Uri(urlInput.val());
			var query = url.search(true);
			var productId = query.id || query.mallstItemId;

			if (productId) {
				$.ajax(Configuration.ajaxUrl, {
					'type': 'POST',
					'data': {
						'action': 'GetProductById',
						'id': productId
					},
					'dataType': 'json',
					'success': function(data) {
						var product = data.taobao.item;
						if (product) {
							// Found the product
							var box = new ProductDetailBox({
								name: product.title,
								unitPrice: parseFloat(product.price),
								picUrl: product.pic_url,
								exchangeRate: data.exchangeRate,
								domesticShippingCost: data.domesticShippingCost
							});
							$(id + ' .product-detail-box-container').html(box.createDom());
							box.onDomCreated();
						} else {
							// TODO: implement error handling
							alert('Could not find the product');
						}
						
					}
				});
			} else {
				// TODO: log unrecognized url
			}
		});
	};

})(jQuery, URI, this['daigou.Configuration'], this['daigou.Dom'], this['daigou.ProductDetailBox']);
