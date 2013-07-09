(function($, Dom) {
	'use strict';
	var attr = Dom.getAttributeString;
	var cls = Dom.getClasses;

	var Guide = window['daigou.Guide'] = function(items) {
		this._items = items;
		this._slideIndex = 0;
	};

	var prototype = Guide.prototype;

	prototype.createDom = function() {
		var id = this._id = Dom.getId();
		var items = this._items;

		var iconsDom = [];
		for (var i = 0, len = items.length; i < len; i++) {
			var item = items[i];
			var classes = (i === 0) ? ['icon', 'selected'] : ['icon'];
			iconsDom.push(
				'<img ', cls(classes), attr('src', item.icon), ' />'
			);
		}

		return [
			'<div ', attr('id', id), attr('class', 'daigou-guide'), '>',
				'<div class="slide-container">',
					'<a class="arrow-left" href="#"></a>',
					'<a class="arrow-right" href="#"></a>',
				'</div>',
				'<div class="icon-container">',
					iconsDom.join(''),
				'</div>',
			'</div>'
		].join('');
	};

	prototype.onDomCreated = function() {

	};

	$(document).ready(function() {
		// TODO: replace it with real data
		var guide = new Guide([
			{ icon: '../img/loading.gif' },
			{ icon: '../img/loading.gif' }
		]);

		$('.daigou-guide').replaceWith(guide.createDom());
		guide.onDomCreated();

	});

})(jQuery, window['daigou.Dom']);
