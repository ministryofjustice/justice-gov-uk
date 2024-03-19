import jQuery from 'jquery';
import {
  SetCookie,
  DeleteCookie,
  getCookieVal,
  GetCookie,
  SetCookieConsent
} from "./cookies";

//variables
var moj_rhs_panels = [
  ['PANELS.mostPopular', '#panel-mostPopular'],
  ['PANELS.related', '#panel-relatedContent'],
  ['PANELS.emailAlerts', '#panel-emailAlerts'],
  ['PANELS.findForm', '#panel-findForm'],
  ['PANELS.findCourtForm', '#panel-findCourtForm'],
  ['STDATA.ContactSwitch', '#panel-STContact'],
  ['SD.SEARCHSwitch', '#panel-SDsearch']
  //['LPA.LPASwitch','#panel-lpa']
];

/*
  replaces select tags with custom versions
  optional arg allows this to run on a separate piece of html (eg. ajax calls)
*/
function nselect(html) {
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
      jQuery(nselect_ul).append('<li class="option' + j + '"><span>' + jQuery(this).html() + '</span><input type="hidden" value="' + jQuery(this).val() + '" /></li>');
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


jQuery.noConflict();
jQuery(document).ready(function () {
  //mobile/full version links (requires cookies.js)
  jQuery('#links-top').append('<li class="device-only">.</li>');
  jQuery('#links-top').append('<li class="device-only"><form id="mqueries-sel" method="post"><input id="mqueries-val" name="mqueries-val" type="hidden" value="" /></form></li>');
  if (GetCookie("moj-mqueries-val") != "true") {
    jQuery('#mqueries-val').val('full');
    jQuery('#mqueries-sel').append('<a href="#">Full site</a>');
  } else {
    jQuery('#mqueries-val').val('mobile');
    jQuery('#mqueries-sel').append('<a href="#">Mobile site</a>');
    jQuery('#links-top li').removeClass('device-only');
  }
  jQuery('#mqueries-sel a').click(function (e) {
    e.preventDefault();
    jQuery(this).closest('form').submit();
  });
  if (GetCookie('moj-consent') != "true") {
    //define banner
    jQuery('#cookieDirective').append('<div class="cookie-policy"><img src="./?a=55304" width="56" height="64" style="float:left; padding:9px;"><div class="explanation"><p><strong>We use cookies on this website to enable social sharing and monitor site usage. </strong></p><p>Disabling cookies will stop social sharing and prevent us monitoring site usage.</p></div><div id="accept-cookies"><form class="styled" id="set-cookie"><input type="button" name="opt-in" id="opt-in" value="Thanks, I&rsquo;ve read this" class="go-btn"></form><p><a href="/privacy/cookies/">How to manage cookies</a></p></div></div>');
    //show banner
    jQuery('#cookieDirective').show();
    //hide function
    jQuery('input#opt-in').click(function () {
      jQuery('div#cookieDirective').slideUp(800);
    });
    //set cookie
    SetCookieConsent("moj-consent", "true", "730");
  }

  //tab-group
  jQuery('.tab-group .tabs').each(function () {
    //get tabs for this group
    jQuery(this).children('li').each(function (i) {
      jQuery(this).click(function (e, auto) {
        if (typeof auto == "undefined") jQuery(document).stopTime('highlight');
        e.preventDefault();
        var thisTabGrp = jQuery(this).closest('.tab-group');
        //toggle selected tab
        jQuery(thisTabGrp).children('.tabs').children('li.selected').removeClass();
        jQuery(this).addClass('selected');
        //toggle tab contents
        jQuery(thisTabGrp).children('.tab-content').children().each(function (j) {
          i == j ? jQuery(this).fadeIn(250, 'swing') : jQuery(this).fadeOut(250, 'swing');
        });
      });
    });
  });

  //top nav sub
  jQuery('.menu-top li[class!="more"] .flyout-container div[class^="content"]').each(function () {
    var cols = 1;
    var ul = '<ul>';
    var li_count = jQuery(this).find('ul[class!="view-all"] li').length;
    jQuery(this).find('ul[class!="view-all"] li').each(function (i) {
      ul += '<li>' + jQuery(this).html() + '</li>';
      if ((i + 1) % 9 == 0 && (i + 1) < li_count) {
        ul += '</ul><ul>';
        cols++;
      }
    });
    ul += "</ul>";
    jQuery(this).children('ul[class!="view-all"]').remove();
    jQuery(this).prepend(ul);
    jQuery(this).attr('class', 'content-' + cols + 'col');
  });

  //top menu persistent hover
  jQuery('.menu-top .flyout-container,.menu-top .finish').hover(function () {
    jQuery(this).closest('li').children('a').toggleClass('active');
  });

  //top menu clear empty flyovers
  jQuery('.flyout-container ul[class!="view-all"]').each(function () {
    if (jQuery(this).children('li').length == 0) {
      var _parent = jQuery(this).closest('.flyout-container').parent();
      jQuery(_parent).children('.flyout-container,.finish,span').hide();
    }
  });

  //highlight auto-cycle
  if (jQuery('#highlight').length > 0)
    jQuery(document).everyTime(5000, 'highlight', function () {
      var index = jQuery('#highlight .tabs li.selected').index();
      var size = jQuery('#highlight .tabs li').length;
      index < size - 1 ? jQuery('#highlight .tabs li:eq(' + (index + 1) + ')').trigger('click', ['auto']) : jQuery('#highlight .tabs li:eq(0)').trigger('click', ['auto']);
    });

  //split lv2-listing
  if (jQuery('div.lv2-listing').length > 0) {
    var ul = '<ul>';
    var li_count = jQuery('div.lv2-listing ul li').length;
    var split = li_count % 2 == 0 ? li_count / 2 : Math.ceil(li_count / 2);
    jQuery('div.lv2-listing ul li').each(function (i) {
      ul += '<li>' + jQuery(this).html() + '</li>';
      if ((i + 1) == split) ul += '</ul><ul>';
    });
    ul += "</ul>";
    jQuery('div.lv2-listing ul').remove();
    jQuery('div.lv2-listing').append(ul);
  }

  //flickr
  if (jQuery('#flickr').length > 0) {
    jQuery.getJSON("http://pipes.yahoo.com/pipes/pipe.run?RSSfeed=http%3A%2F%2Fapi.flickr.com%2Fservices%2Ffeeds%2Fphotos_public.gne%3Fid%3D27847306%40N06%26lang%3Den-us%26format%3Drss_200&Size=Medium&_id=e3db00846a368e0a89f0dcda78368272&_render=json", function (data) {
      jQuery.each(data.value.items, function (i, item) {
        jQuery("<img/>").attr("src", item['media:content'].url.replace('http', 'https')).attr("alt", item['media:title']).appendTo("#flickr").wrap("<li></li>");
      });
      jQuery('#flickr').jcarousel({
        itemFallbackDimension: 432
      });
    });
  }

  //custom selects
  nselect();

  //news section selected state
  jQuery('#section-sub li').each(function () {
    if (jQuery(this).children('a').html().replace('All news', 'News') == jQuery('#breadcrumb li:last a').html()) jQuery(this).addClass('selected');
  });

  //rhs panels switch
  jQuery.each(moj_rhs_panels, function () {
    if (jQuery('meta[name="' + jQuery(this)[0] + '"]').length > 0 && jQuery('meta[name="' + jQuery(this)[0] + '"]').attr('content') == 1) jQuery(jQuery(this)[1]).show();
  });

  //most popular widget
  if (jQuery('body.home #popular-wrapper').length > 0) {
    //load widget (home)
    jQuery('#popular-wrapper').load('/?a=31472&SQ_DESIGN_NAME=blank&SQ_PAINT_NAME=blank&source=' + jQuery('meta[name="MostPopularOverride"]').attr('content'));
  }
  else if (jQuery('meta[name="PANELS.mostPopular"]').attr('content') == 1) {
    //load widget (panels)
    jQuery('#panel-mostPopular-wrapper').load('/?a=31431&SQ_DESIGN_NAME=blank&SQ_PAINT_NAME=blank&source=' + jQuery('meta[name="MostPopularOverride"]').attr('content'), function () {
      jQuery('#panel-mostPopular').show();
    });
  }

  //related content widget
  if (jQuery('meta[name="PANELS.related"]').attr('content') == 1) {
    //load widget
    jQuery('#panel-relatedContent-wrapper').load('/?a=31357&SQ_DESIGN_NAME=blank&SQ_PAINT_NAME=blank&source=' + jQuery('meta[name="RelatedContentOverride"]').attr('content') + '&assetname=' + jQuery('meta[name="DC.title"]').attr('content').replace(/\s+/g, '-'), function () {
      jQuery('#panel-relatedContent').show();
    });
  }

  //tribunals search
  jQuery('form#tribunal').submit(function (e) {
    e.preventDefault();
    window.location = jQuery(this).find('select').val();
  });

  //clear filter button
  jQuery('.filter .go-btn-grey').click(function () {
    var filter = jQuery(this).closest('.filter');
    jQuery(filter).find('select').each(function () {
      jQuery(this).children('option:first-child').attr('selected', 'selected').trigger('change');
    });
    jQuery(filter).children('form').submit();
  });

  //find a court panel
  var courtFinder = '';

  if (jQuery('#panel-findCourtForm').length > 0)
    courtFinder = jQuery('#panel-findCourtForm').find('form');
  else if (jQuery('#FormFinderForm').length > 0)
    courtFinder = jQuery('#FormFinderForm');

  if (courtFinder.length > 0) {
    var boxA = jQuery('#court_forms_num');
    var boxB = jQuery('#court_forms_title');
    var boxC = jQuery('#court_work_type');

    jQuery(courtFinder).find('input[type=submit]').click(function (e) {
      e.preventDefault();
      if (boxA.val() != '' || boxB.val() != '' || boxC.val() != '') {
        jQuery(courtFinder).submit();
      }
    });

    function boxClear(box) {
      if (typeof box == "undefined") return;

      switch (box) {
        case "boxA":
          boxA.val('');
          break;
        case "boxB":
          boxB.val('');
          break;
        case "boxC":
          boxC.val(jQuery('#rhs-find-court-types option:first').val()).trigger('change', ['clear']);
          break;
      }
    }

    boxA.keyup(function () {
      boxClear('boxB');
      boxClear('boxC');
    });

    boxB.keyup(function () {
      boxClear('boxA');
      boxClear('boxC');
    });

    boxC.change(function (e, clear) {
      if (typeof clear == "undefined") {
        boxClear('boxA');
        boxClear('boxB');
      }
    });
  }

  //phone news listing filter
  jQuery('.date-bar form select').change(function () {
    jQuery(this).closest('form').submit();
  });

  //court finder
  jQuery.ajax({
    url: _mojCourtFinderSearchXML,
    dataType: "xml",
    success: function (xmlResponse) {
      var data = jQuery("crt", xmlResponse).map(function () {
        return {
          value: jQuery("cname", this).text(),
          id: jQuery("id", this).text()
        };
      }).get();

      jQuery("#courtcomplete").autocomplete({
        source: data,
        minLength: 0,
        select: function (event, ui) {
          jQuery('#court_id').val(ui.item.id);
        }
      });
    }
  });
  jQuery("#court-search").click(function (event) {
    event.preventDefault();

    var courtId = jQuery('#court_id').val();


    if (courtId.length > 0) {
      jQuery('#CourtListForm').attr('action', 'http://hmctscourtfinder.justice.gov.uk/HMCTS/Search.do');
      jQuery('#CourtListForm').submit();
    } else {
      jQuery("#crt_msg").show();
    }
  });
});