import jQuery from 'jquery';
import nselect from "./nselect";

// jQuery.noConflict();
jQuery(document).ready(function () {

  /**
   * Custom selects are used on the search page.
   */

  nselect();

  /**
   * The following code is legacy and is not yet fully understood.
   */

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
  if (jQuery('#highlight').length > 0) {
    setInterval(function() {
      var index = jQuery('#highlight .tabs li.selected').index();
      var size = jQuery('#highlight .tabs li').length;
      index < size - 1 ? jQuery('#highlight .tabs li:eq(' + (index + 1) + ')').trigger('click', ['auto']) : jQuery('#highlight .tabs li:eq(0)').trigger('click', ['auto']);
    }, 5000);
  }

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

  // Migration: News is now on gov.uk. In case we ned this do it with PHP.
  //news section selected state
  // jQuery('#section-sub li').each(function () {
  //   if (jQuery(this).children('a').html().replace('All news', 'News') == jQuery('#breadcrumb li:last a').html()) jQuery(this).addClass('selected');
  // });

  // Migration: don't use panels switch. Use php to enable/disable panels.
  //variables
  // var moj_rhs_panels = [
  //   ['PANELS.mostPopular', '#panel-mostPopular'],
  //   ['PANELS.related', '#panel-relatedContent'],
  //   ['PANELS.emailAlerts', '#panel-emailAlerts'],
  //   ['PANELS.findForm', '#panel-findForm'],
  //   ['PANELS.findCourtForm', '#panel-findCourtForm'],
  //   ['STDATA.ContactSwitch', '#panel-STContact'],
  //   ['SD.SEARCHSwitch', '#panel-SDsearch']
  //   //['LPA.LPASwitch','#panel-lpa']
  // ];

  // Migration: don't use panels switch. Use php to enable/disable panels.
  //rhs panels switch
  // jQuery.each(moj_rhs_panels, function () {
  //   if (jQuery('meta[name="' + jQuery(this)[0] + '"]').length > 0 && jQuery('meta[name="' + jQuery(this)[0] + '"]').attr('content') == 1) jQuery(jQuery(this)[1]).show();
  // });

  // Migration: don't load content in php template, not with ajax.
  //most popular widget
  // if (jQuery('body.home #popular-wrapper').length > 0) {
  //   //load widget (home)
  //   jQuery('#popular-wrapper').load('/?a=31472&SQ_DESIGN_NAME=blank&SQ_PAINT_NAME=blank&source=' + jQuery('meta[name="MostPopularOverride"]').attr('content'));
  // }
  // else if (jQuery('meta[name="PANELS.mostPopular"]').attr('content') == 1) {
  //   //load widget (panels)
  //   jQuery('#panel-mostPopular-wrapper').load('/?a=31431&SQ_DESIGN_NAME=blank&SQ_PAINT_NAME=blank&source=' + jQuery('meta[name="MostPopularOverride"]').attr('content'), function () {
  //     jQuery('#panel-mostPopular').show();
  //   });
  // }

  // Migration: don't load content in php template, not with ajax.
  //related content widget
  // if (jQuery('meta[name="PANELS.related"]').attr('content') == 1) {
  //   //load widget
  //   jQuery('#panel-relatedContent-wrapper').load('/?a=31357&SQ_DESIGN_NAME=blank&SQ_PAINT_NAME=blank&source=' + jQuery('meta[name="RelatedContentOverride"]').attr('content') + '&assetname=' + jQuery('meta[name="DC.title"]').attr('content').replace(/\s+/g, '-'), function () {
  //     jQuery('#panel-relatedContent').show();
  //   });
  // }

  //tribunals search
  // jQuery('form#tribunal').submit(function (e) {
  //   e.preventDefault();
  //   window.location = jQuery(this).find('select').val();
  // });

  //clear filter button
  // jQuery('.filter .go-btn-grey').click(function () {
  //   var filter = jQuery(this).closest('.filter');
  //   jQuery(filter).find('select').each(function () {
  //     jQuery(this).children('option:first-child').attr('selected', 'selected').trigger('change');
  //   });
  //   jQuery(filter).children('form').submit();
  // });

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
  // Migration: disable temporarily.
  // jQuery.ajax({
  //   url: _mojCourtFinderSearchXML,
  //   dataType: "xml",
  //   success: function (xmlResponse) {
  //     var data = jQuery("crt", xmlResponse).map(function () {
  //       return {
  //         value: jQuery("cname", this).text(),
  //         id: jQuery("id", this).text()
  //       };
  //     }).get();

  //     jQuery("#courtcomplete").autocomplete({
  //       source: data,
  //       minLength: 0,
  //       select: function (event, ui) {
  //         jQuery('#court_id').val(ui.item.id);
  //       }
  //     });
  //   }
  // });

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
