(function($, Configuration, Dom, LoadingMask) {
  var attr = Dom.getAttributeString;
  var DEFAULT_URL_TEXT = '粘贴淘宝商品链接';
  var DEFAULT_ERROR_MSG = '找不到您所要的商品哟，亲!请人肉发送至request@daigouge.com';
  var TXT_BUTTON = '代购吧';
  var PATTERN_URL = /[&|?](?:id|mallstItemId)=(\d+)/;

  var ProductUrlInput = this['daigou.ProductUrlInput'] = function() {};

  var prototype = ProductUrlInput.prototype;
  prototype.createDom = function() {
    var id = this._id = Dom.getId();

    return [
      '<form ', attr('id', id) , attr('class', 'daigou-product-url-input'), '>',
        '<div class="url-input-container">',
          '<input name="url" type="text"', attr('value', DEFAULT_URL_TEXT), '/> ',
        '</div>',
        '<div class="button-container">',
          '<button type="submit">', TXT_BUTTON, '</button>',
        '</div>',
      '</form>'
    ].join('');
  };

  prototype.onDomCreated = function() {
    var id = '#' + this._id;
    var button = $(id + ' .url-input-container button');
    var urlInput = $(id + ' .url-input-container input');
    var me = this;
    var loadingMask = new LoadingMask(id);
    loadingMask.createDom();

    urlInput.mouseover(function() {
      urlInput.select();
    });

    urlInput.mouseup(function(e) {
      urlInput.select();
      e.preventDefault();
    });

    $(id).submit(function(evt) {
      var matches = PATTERN_URL.exec(urlInput.val());

      if (matches) {
        var productId = matches[1];
        loadingMask.show();
        $.ajax(Configuration.ajaxUrl, {
          'type': 'POST',
          'data': {
            'action': 'GetProductById',
            'id': productId
          },
          'dataType': 'json',
          'error': function() {
            loadingMask.hide();
          },
          'success': function(data) {
            var url = data.productUrl;
            if (url) {
              window.location = url;
            } else {
              // TODO: error handling
              loadingMask.hide();
              var error = data.error || DEFAULT_ERROR_MSG;
              alert(error);
            }
          }
        });
      } else {
        // TODO: log unrecognized url
        alert(DEFAULT_ERROR_MSG);
      }
      evt.preventDefault();
    });
  };

})(jQuery, window['daigou.Configuration'], window['daigou.Dom'], window['daigou.LoadingMask']);
