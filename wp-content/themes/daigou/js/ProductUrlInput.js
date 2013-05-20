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

		urlInput.focus(function(event) {
			urlInput.select();
		});

		urlInput.bind('mouseup', function(event) {
			urlInput.select();
			event.preventDefault();
		});

		button.click(function() {
			var url = new Uri(urlInput.val());
			var query = url.search(true);
			var id = query.id || query.mallstItemId;

			if (id) {
				$.ajax(Configuration.ajaxUrl, {
					'data': {
						'action': 'GetProductById',
						'id': id
					},
					'dataType': 'json',
					'success': function(data) {
						var box = new ProductDetailBox({
							name: 'ShengMin Zhang',
							unitPrice: 499999.00,
							picUrl: 'https://fbcdn-profile-a.akamaihd.net/hprofile-ak-ash3/c31.46.388.388/s160x160/559947_10151170463151123_1418077920_n.jpg',
							exchangeRate: 6,
							domesticShippingCost: 22
						});
						$(id + ' .product-detail-box-container').html(box.createDom());
						box.onDomCreated();
					}
				});
			} else {
				// TODO: log unrecognized url
			}
		});
	};

})(jQuery, URI, DaigouConfiguration, this['daigou.Dom'], this['daigou.ProductDetailBox']);
