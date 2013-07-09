(function($, Dom) {
  'use strict';
  var attr = Dom.getAttributeString;
  var style = Dom.getStyle;

  var Guide = window['daigou.Guide'] = function(items, settings) {
    // Flattens the slides
    var slides = this._slides = [];
    // Fast forward using the icon
    var icons = this._icons = [];

    for (var i = 0, iconCount = items.length; i < iconCount; i++) {
      var item = items[i];
      var iconSlides = item.slides;
      icons[i] = { url: item.icon, slideIndex: slides.length };

      for (var j = 0, slideCount = iconSlides.length; j < slideCount; j++) {
        var slide = iconSlides[j];
        slides.push({ url: slide.url, text: slide.text, iconIndex: i });
      }
    }

    this._slideIndex = this._iconIndex = -1;
  };

  var prototype = Guide.prototype;

  prototype.updateDom = function(newSlideIndex, newIconIndex) {
    // var slideIndex = this._slideIndex;
    // if (newSlideIndex === slideIndex) {
    //   return;
    // }

    // var id = '#' + this._id;
    // // Shifts in the correct slide
    // $(id + ' .slides-container').css('margin-left', -100 * newSlideIndex + '%');
    // this._slideIndex = newSlideIndex;

    // var iconIndex = this._iconIndex;
    // if (iconIndex === newIconIndex) {
    //   return;
    // }


    // this._iconIndex = newIconIndex;
  };

  function createSlidesDom(slides) {
    var buffer = [];
    var len = slides.length;
    var width = 100 / slides.length;
    for (var i = 0; i < len; i++) {
      var slide = slides[i];
      buffer.push(
        '<div class="slide-container"', style({ width: width + '%'}), '>',
          '<img class="slide" ', attr('src', slide.url), '/>',
          '<div class="slide-text">', slide.text, '</div>',
        '</div>'
      );
    }

    return buffer.join('');
  }

  function createIconsDom(icons) {
    var buffer = [];
    for (var i = 0, len = icons.length; i < len; i++) {
      var icon = icons[i];
      buffer.push(
        '<img class="icon"', attr('src', icon.url), ' />'
      );
    }
    return buffer.join('');
  }

  prototype.createDom = function() {
    var id = this._id = Dom.getId();

    return [
      '<div', attr('id', id), attr('class', 'daigou-guide'), '>',
        '<div class="slide-viewer">',
          '<div class="slides-container group"', style({ width: 100 * this._slides.length + '%' }),'>',
            createSlidesDom(this._slides),
          '</div>',
          '<a class="arrow-left" href="#"></a>',
          '<a class="arrow-right" href="#"></a>',
        '</div>',
        '<div class="icon-container">',
          createIconsDom(this._icons),
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
          { url: '../img/arrow-right.png', text: '1 0' },
          { url: '../img/arrow-left.png', text: '1 1' }
        ]
      }
    ]);

    $('.daigou-guide').replaceWith(guide.createDom());
    //guide.onDomCreated();

  });

})(jQuery, window['daigou.Dom']);
