(function($, Dom) {
	var attr = Dom.getAttributeString;
	var IMAGE_SIZE = 32;

	var LoadingMask = this['daigou.LoadingMask'] = function(context) {
		this._context = $(context);
	};

	var prototype = LoadingMask.prototype;
	prototype.createDom = function() {
		var id = this._id = Dom.getId();
		var dom = [
			'<div ', attr('class', 'daigou-loading-mask'), attr('id', id), '>',
				'<div ', attr('class', 'loading-indicator'), '></div>',
			'</div>'
		].join('');

		this._context.offsetParent().append(dom);
		$('#' + id).hide();
		return dom;
	};

	prototype.show = function() {
		var context = this._context;
		var contextElement = context.get(0);
		var height = context.outerHeight();
		var width = context.outerWidth();
		var id = '#' + this._id;

		$(id + ' .loading-indicator')
			.css('left', (width - IMAGE_SIZE) / 2 + 'px')
			.css('top', (height - IMAGE_SIZE) / 2 + 'px');

		$(id)
			.width(width)
			.height(height)
			.css('left', contextElement.offsetLeft + 'px')
			.css('top', contextElement.offsetTop + 'px')
			.show();
	};

	prototype.hide = function() {
		$('#' + this._id).hide();
	}
})(jQuery, window['daigou.Dom']);
