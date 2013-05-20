(function() {
	var PREFIX = '-daigou-id-'
	var id = 0;

	var Dom = this['daigou.Dom'] = {
		/**
		 * Gets a unique ID
		 */
		getId: function() {
			return PREFIX + id++;
		},

		getAttributeString: function(name, value) {
			return name + '="' + value + '" ';
		}
	};
})();