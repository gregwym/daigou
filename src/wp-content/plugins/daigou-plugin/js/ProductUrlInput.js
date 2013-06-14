(function($, Uri, Configuration, Dom, LoadingMask) {
  var attr = Dom.getAttributeString;
  var DEFAULT_URL_TEXT = 'Paste in a TaoBao URL';

  var ProductUrlInput = this['daigou.ProductUrlInput'] = function() {};

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
    var loadingMask = new LoadingMask(id);
    loadingMask.createDom();

    urlInput.mouseover(function() {
      urlInput.select();
    });

    urlInput.mouseup(function(e) {
      urlInput.select();
      e.preventDefault();
    });

    button.click(function() {
      // TODO: change the way to parse URL
      var url = new Uri(urlInput.val());
      var query = url.search(true);
      var productId = query.id || query.mallstItemId;

      if (productId) {
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
              alert('We could not add the product to shopping cart, please try again');
            }
          }
        });
      } else {
        // TODO: log unrecognized url
        alert('We could not find the product you are looking for.')
      }
    });
  };

})(jQuery, URI, window['daigou.Configuration'], window['daigou.Dom'], window['daigou.LoadingMask']);
