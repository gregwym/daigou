(function($, Dom) {
	var attr = Dom.getAttributeString;

	var ProductDetailBox = this['daigou.ProductDetailBox'] = function(data) {
		this._data = data;
	};

	var prototype = ProductDetailBox.prototype;
	prototype.createDom = function() {
		var id = this._id = Dom.getId();
		var data = this._data;
		var cost = this._getCost(1);

		return ['\
			<div ', attr('id', id), attr('class', 'daigou-product-detail-box'), '> \
				<div class="header">', data.name, '</div> \
				<div class="group"> \
					<div class="picture-box"> \
						<img ', attr('src', data.picUrl), '/> \
					</div> \
					<div class="form"> \
						<div class="row"> \
							<div class="name">Product Price (RMB):</div> \
							<div class="value"> \
								<input type="text" disabled="true" ', attr('value', data.unitPrice.toFixed(2)), '/> \
							</div> \
						</div> \
						<div class="row"> \
							<div class="name">Domestic Shipping Fee (RMB):</div> \
							<div class="value"> \
								<input type="text" disabled="true" ', attr('value', data.domesticShippingCost.toFixed(2)), '/> \
							</div> \
						</div> \
						<div class="row"> \
							<div class="name">Total Cost (RMB):</div> \
							<div class="value"> \
								<input type="text" name="costInRmb" disabled="true" ', attr('value', cost.rmb.toFixed(2)), '/> \
							</div> \
						</div> \
						<div class="row"> \
							<div class="name">Total Cost (CAD):</div> \
							<div class="value"> \
								<input type="text" name="costInCad" disabled="true" ', attr('value', cost.cad.toFixed(2)), '/> \
							</div> \
						</div> \
						<div class="row"> \
							<div class="name">Quantity:</div> \
							<div class="value"> \
								<input name="quantity" type="text" ', attr('value', 1), '/> \
							</div> \
						</div> \
						<div class="row"> \
							<div class="name">Comments:</div> \
							<div> \
								<textarea></textarea> \
							</div> \
						</div> \
					</div> \
				</div> \
				<div class="footer"> \
					<button class="add-to-cart-button">Add to cart</button> \
				</div> \
			</div> \
		'].join('');
	};

	prototype.onDomCreated = function() {
		var id = '#' + this._id;
		var me = this;
		var quantityInput = $(id + ' [name="quantity"]');
		var costInRmbInput = $(id + ' [name="costInRmb"]');
		var costInCadInput = $(id + ' [name="costInCad"]');

		quantityInput.change(function() {
			var quantity = parseInt(quantityInput.val(), 10);
			if (quantity !== NaN) {
				// TODO: validation
				var cost = me._getCost(quantity);
				costInCadInput.val(cost.cad.toFixed(2));
				costInRmbInput.val(cost.rmb.toFixed(2));
			}

		});

		$(id + ' button').click(function() {
			// TODO: implement add to shopping cart
			alert('Not yet implemented');
		});
	};

	prototype._getCost = function(quantity) {
		var data = this._data;
		var rmb = quantity * data.unitPrice + data.domesticShippingCost;
		return {
			'rmb': rmb,
			'cad': rmb * data.exchangeRate
		};
	};

})(jQuery, this['daigou.Dom']);