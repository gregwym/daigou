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
          '<div class="arrow-container-left">',
            '<a class="arrow arrow-left" href="#"></a>',
          '</div>',
          '<div class="arrow-container-right">',
            '<a class="arrow arrow-right" href="#"></a>',
          '</div>',
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

    $(id + ' .arrow-container-left').click(function(evt) {
      var slideIndex = Math.max(0, me._slideIndex - 1);
      me._updateDom(slideIndex, me._slides[slideIndex].iconIndex);
      evt.preventDefault();
    });

    $(id + ' .arrow-container-right').click(function(evt) {
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
            { url: '/wp-content/images/guide-slide-00.jpg', text: '前往淘宝网，选中称心的商品连接，复制后回到“袋狗哥”' }
          ]
        },
        {
          icon: '/wp-content/images/guide-icon-1.png',
          slides: [
            { url: '/wp-content/images/guide-slide-01.jpg', text: '粘贴所选商品链接至方框内，按“代购吧”开始代购哟～～～～' },
            { url: '/wp-content/images/guide-slide-02.jpg', text: '在方框内填写您所需要的颜色，尺寸，数量' },
            { url: '/wp-content/images/guide-slide-03.jpg', text: '由于淘宝API无法自动抓取特价的价格，所以如果有特价，还请填上特价的价格，我们会稍后给您手动调整 :)' },
            { url: '/wp-content/images/guide-slide-04.jpg', text: '您可以在商品介绍中看到其人民币价格，以及自动抓去的汇率情况；如确定要此商品，请点击“加入购物车”' },
            { url: '/wp-content/images/guide-slide-05.jpg', text: '若您需要继续代购其他商品，请选择“继续购物”' },
            { url: '/wp-content/images/guide-slide-06.jpg', text: '如需结算，请选择“前往购物车”  很简单吧^_^' },
          ]
        },
        {
          icon: '/wp-content/images/guide-icon-2.png',
          slides: [
            { url: '/wp-content/images/guide-slide-07.jpg', text: '前往购物车，确认商品信息，进行结算；该结算只包含国内商品价格，国内运费将和国际运费在货到时一起结算' },
            { url: '/wp-content/images/guide-slide-08.jpg', text: '可以选取送货的方式，自取或者送货上门' }
          ]
        },
        {
          icon: '/wp-content/images/guide-icon-3.png',
          slides: [
            { url: '/wp-content/images/guide-slide-09.jpg', text: '若您时第一次使用，需要填写相关信息，方便我们配送和联系您' },
            { url: '/wp-content/images/guide-slide-10.jpg', text: '运送地址和帐单地址不同，账单地址为您信用卡的地址；运送地址为您希望我们送到的地址' },
            { url: '/wp-content/images/guide-slide-11.jpg', text: '现在注册，即刻享受免代购费的服务哟～～～您的用户名是您所填的邮件' },
            { url: '/wp-content/images/guide-slide-12.jpg', text: '订单总揽～～' }
          ]
        },
        {
          icon: '/wp-content/images/guide-icon-4.png',
          slides: [
            { url: '/wp-content/images/guide-slide-13.jpg', text: '如果在未能如实抓去价格，请点击“价格调整”' },
            { url: '/wp-content/images/guide-slide-14.jpg', text: '支持用信用卡或PayPal直接付款，方便，安全，快捷 :)' }
          ]
        }
      ]);

      $(this).append(guide.createDom());
      guide.onDomCreated();
    });
  });

})(jQuery, window['daigou.Dom']);
