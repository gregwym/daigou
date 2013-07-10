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

  prototype._updateDom = function(newSlideIndex, newIconIndex) {
    var slideIndex = this._slideIndex;
    if (newSlideIndex === slideIndex) {
      return;
    }

    var id = '#' + this._id;
    // Shifts in the correct slide
    $(id + ' .slides-container').css('margin-left', -100 * newSlideIndex + '%');
    $(id + ' .arrow-left').toggle(newSlideIndex > 0);
    $(id + ' .arrow-right').toggle(newSlideIndex < this._slides.length - 1);
    this._slideIndex = newSlideIndex;

    var iconIndex = this._iconIndex;
    if (iconIndex === newIconIndex) {
      return;
    }

    $(id + ' .icon').eq(iconIndex).removeClass('selected');
    $(id + ' .icon').eq(newIconIndex).addClass('selected');
    this._iconIndex = newIconIndex;
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
        '<div class="icons-container">',
          createIconsDom(this._icons),
        '</div>',
      '</div>'
    ].join('');
  };

  prototype.onDomCreated = function() {
    var me = this;
    var id = '#' + this._id;

    $(id + ' .arrow-left').click(function(evt) {
      var slideIndex = Math.max(0, me._slideIndex - 1);
      me._updateDom(slideIndex, me._slides[slideIndex].iconIndex);
      evt.preventDefault();
    });

    $(id + ' .arrow-right').click(function(evt) {
      var slideIndex = Math.min(me._slideIndex + 1, me._slides.length - 1);
      me._updateDom(slideIndex, me._slides[slideIndex].iconIndex);
      evt.preventDefault();
    });

    $(id + ' .icon').click(function(evt) {
      var iconIndex = $(this).index();
      me._updateDom(me._icons[iconIndex].slideIndex, iconIndex);
    });

    this._updateDom(0, 0);
  };

  $(document).ready(function() {
    $('#guide-container').each(function() {
      var guide = new Guide([
        {
          icon: '/wp-content/images/guide-icon-0.png',
          slides: [
            { url: '/wp-content/images/guide-slide-0.jpg', text: '' },
            { url: '/wp-content/images/guide-slide-1.jpg', text: '' },
            { url: '/wp-content/images/guide-slide-2.jpg', text: '' },
            { url: '/wp-content/images/guide-slide-3.jpg', text: '' },
            { url: '/wp-content/images/guide-slide-4.jpg', text: '' }
          ]
        }
      ]);

      $(this).append(guide.createDom());
      guide.onDomCreated();
    });
  });

})(jQuery, window['daigou.Dom']);
