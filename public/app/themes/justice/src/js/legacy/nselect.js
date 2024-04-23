import jQuery from 'jquery';

/*
  replaces select tags with custom versions
  optional arg allows this to run on a separate piece of html (eg. ajax calls)
*/
export default function nselect(html) {
  var offset = 0;

  if (typeof (html) == 'undefined' || html.length == 0)
    html = jQuery('body');
  else
    offset = jQuery('[id^="nselect"]').length;

  jQuery(html).find('select').each(function (i) {
    if (typeof jQuery(this).attr('class') != 'undefined' && jQuery(this).attr('class').indexOf('no-nselect') > -1) return;

    var pos = i + offset;
    var select = this;
    var nselect = "#nselect" + pos;
    var nselect_current = nselect + ' .current';
    var nselect_ul = nselect + ' ul';
    var nselect_li = nselect_ul + ' li';
    //hide select
    jQuery(this).hide();
    //build
    jQuery(this).after('<div id="nselect' + pos + '" class="nselect" style="z-index:' + (1000 - pos) + ';" tabindex="0"><div class="current"></div><ul class="inner-list" style="display:none;"></ul></div>');
    var selected_val = '';
    jQuery(select).children('option').each(function (j) {
      // Migration edit - wrap value in encodeURIComponent to handle special characters and prevent XSS.
      jQuery(nselect_ul).append('<li class="option' + j + '"><span>' + jQuery(this).html() + '</span><input type="hidden" value="' + encodeURIComponent(jQuery(this).val()) + '" /></li>');
      // jQuery(nselect_ul).append('<li class="option' + j + '"><span>' + jQuery(this).html() + '</span><input type="hidden" value="' + jQuery(this).val() + '" /></li>');
      if (jQuery(this).is(':selected')) selected_val = jQuery(this).html();
    });
    if (selected_val.length == 0) selected_val = jQuery(nselect_ul + ' li.option0').html();
    jQuery(nselect).children('.current').html(selected_val);
    //events
    jQuery(nselect).focus(function () {
      jQuery(nselect_ul).show();
    });
    jQuery(nselect).blur(function () {
      jQuery(nselect_ul).hide();
      jQuery(nselect_ul).children('li.hover').removeClass('hover');
      jQuery(nselect_ul).children('li.selected').addClass('hover');
    });
    jQuery(nselect).click(function () {
      jQuery(nselect).focus();
    });
    jQuery(nselect).keydown(function (e) {
      switch (e.which) {
        case 13: // enter
          e.preventDefault();
          jQuery(nselect_ul).children('li.hover').click();
          break;
        case 32: // space
          e.preventDefault();
          jQuery(nselect_ul).toggle();
          break;
        case 38: // arrow-up
          e.preventDefault();
          if (!jQuery(nselect_ul).is(":visible")) {
            jQuery(nselect_ul).show();
            break;
          }
          if (jQuery(nselect_ul).children('li.hover').length > 0) {
            var ind = jQuery(nselect_ul).children('li.hover').index();
            if (ind > 0) {
              jQuery(jQuery(nselect_ul).children('li')[ind]).removeClass('hover');
              jQuery(jQuery(nselect_ul).children('li')[ind - 1]).addClass('hover');
            } else {
              jQuery(jQuery(nselect_ul).children('li')[ind]).removeClass('hover');
              jQuery(jQuery(nselect_ul).children('li')[jQuery(nselect_ul).children('li').length - 1]).addClass('hover');
            }
          } else jQuery(jQuery(nselect_ul).children('li')[jQuery(nselect_ul).children('li').length - 1]).addClass('hover');
          break;
        case 40: // arrow-down
          e.preventDefault();
          if (!jQuery(nselect_ul).is(":visible")) {
            jQuery(nselect_ul).show();
            break;
          }
          if (jQuery(nselect_ul).children('li.hover').length > 0) {
            var ind = jQuery(nselect_ul).children('li.hover').index();
            if (ind < jQuery(nselect_ul).children('li').length - 1) {
              jQuery(jQuery(nselect_ul).children('li')[ind]).removeClass('hover');
              jQuery(jQuery(nselect_ul).children('li')[ind + 1]).addClass('hover');
            } else {
              jQuery(jQuery(nselect_ul).children('li')[ind]).removeClass('hover');
              jQuery(jQuery(nselect_ul).children('li')[0]).addClass('hover');
            }
          } else jQuery(jQuery(nselect_ul).children('li')[0]).addClass('hover');
          break;
      }
      if (e.which >= 65 && e.which <= 90) {
        var current = jQuery(nselect_ul).children('li.hover').length > 0 ? jQuery(nselect_ul).children('li.hover').index() : -1;
        var stop = false;
        for (var i = current + 1; i < jQuery(nselect_ul).children('li').length; i++) {
          var _this = jQuery(nselect_ul).children('li')[i];
          if (jQuery(_this).html().charAt(6).toLowerCase() == String.fromCharCode(e.which).toLowerCase()) {
            jQuery(nselect_ul).show();
            jQuery(nselect_ul).children('li.hover').removeClass('hover');
            jQuery(jQuery(nselect_ul).children('li')[i]).addClass('hover');
            stop = true;
            break;
          }
        }
        if (!stop) for (var i = 0; i <= current; i++) {
          var _this = jQuery(nselect_ul).children('li')[i];
          if (jQuery(_this).html().charAt(6).toLowerCase() == String.fromCharCode(e.which).toLowerCase()) {
            jQuery(nselect_ul).show();
            jQuery(nselect_ul).children('li.hover').removeClass('hover');
            jQuery(jQuery(nselect_ul).children('li')[i]).addClass('hover');
            break;
          }
        }
      }
      // Scroll to element position
      var ind = jQuery(nselect_ul).children('li.hover').index();
      var h = 0;
      jQuery(nselect_ul).children('li').each(function (i) {
        if (i == ind) return false;
        if (i > 0) h += jQuery(this).outerHeight();
      });
      jQuery(nselect_ul).scrollTop(h);
    });
    jQuery(nselect_ul).children('li').hover(function () {
      jQuery(nselect_ul).children('li.hover').removeClass('hover');
      jQuery(this).addClass('hover');
    });
    jQuery(nselect_li).click(function (e) {
      e.stopPropagation();
      jQuery(select).val(jQuery(this).children('input').val());
      jQuery(nselect_current).html(jQuery(this).children('span').html());
      jQuery(nselect_ul).hide();
      jQuery(select).trigger('change');
      jQuery(nselect_ul).children('li.hover').removeClass('hover');
      jQuery(nselect_ul).children('li.selected').removeClass('selected');
      jQuery(this).addClass('selected').addClass('hover');
    });
    jQuery(select).change(function () {
      jQuery(nselect_current).html(jQuery(this).children('option:selected').html());
    })
    //move 'device-only' class
    if (jQuery(select).hasClass('device-only')) {
      jQuery(select).removeClass('device-only');
      jQuery(nselect).addClass('device-only');
    }
  });
}
