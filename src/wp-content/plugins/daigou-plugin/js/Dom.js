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
      return ' ' + name + '="' + value + '" ';
    },

    getClasses: function(classes) {
      return Dom.getAttributeString('class', classes.join(' '));
    },

    getStyle: function(styles) {
      var buffer = [];
      for (var style in styles) {
        buffer.push(style, ':', styles[style], ';');
      }
      return Dom.getAttributeString('style', buffer.join(''));
    }
  };
})();
