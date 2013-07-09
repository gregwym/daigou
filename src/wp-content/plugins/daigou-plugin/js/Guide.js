(function($, Dom) {
  'use strict';
  var attr = Dom.getAttributeString;
  var cls = Dom.getClasses;

  var Guide = window['daigou.Guide'] = function(items) {
    this._items = items;
    this._slideIndex = 0;
    this._iconIndex = 0;
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
          '<img class="slide" ', attr('src', items[0].slides[0].url), ' />',
          '<a class="arrow-left" href="#"></a>',
          '<a class="arrow-right" href="#"></a>',
          '<div class="slide-text">', items[0].slides[0].text, '</div>',
        '</div>',
        '<div class="icon-container">',
          iconsDom.join(''),
        '</div>',
      '</div>'
    ].join('');
  };

  prototype.onDomCreated = function() {
    var me = this;
    var id = '#' + this._id;
    var items = this._items;

    var slide = $(id + ' .slide');
    var slideText = $(id + ' .slide-text');

    $(id + ' .arrow-left').click(function(evt) {
      var slideIndex = me._slideIndex - 1;
      var slides = items[me._iconIndex].slides;

      if (slideIndex < 0) {
        slideIndex = slides.length - 1;
      }

      me._slideIndex = slideIndex;
      slide.attr('src', slides[slideIndex].url);
      slideText.text(slides[slideIndex].text);
      evt.preventDefault();
    });

    $(id + ' .arrow-right').click(function(evt) {
      var slideIndex = me._slideIndex + 1;
      var slides = items[me._iconIndex].slides;

      if (slideIndex >= slides.length) {
        slideIndex = 0;
      }

      me._slideIndex = slideIndex;
      slide.attr('src', slides[slideIndex].url);
      slideText.text(slides[slideIndex].text);
      evt.preventDefault();
    });

    var icons = $(id + ' .icon');
    icons.click(function(evt) {
      icons.eq(me._iconIndex).removeClass('selected');
      $(this).addClass('selected');
      var iconIndex = me._iconIndex = $(this).index();
      var slideIndex = me._slideIndex = 0;
      var slides = items[iconIndex].slides;
      slide.attr('src', slides[slideIndex].url);
      slideText.text(slides[slideIndex].text);
    });
  };

  $(document).ready(function() {
    // TODO: replace it with real data
    var guide = new Guide([
      {
        icon: '../img/arrow-left.png',
        slides: [
          { url: '../img/loading.gif', text: ' 0 0' },
          { url: '../img/arrow-left.png', text: '0 1' }
        ]
      },
      {
        icon: '../img/arrow-left.png',
        slides: [
          { url: '../img/arrow-left.png', text: '1 0' },
          { url: '../img/arrow-left.png', text: '1 1' }
        ]
      }
    ]);

    $('.daigou-guide').replaceWith(guide.createDom());
    guide.onDomCreated();

  });

})(jQuery, window['daigou.Dom']);
