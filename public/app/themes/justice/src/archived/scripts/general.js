/*------------------------------------------------------------------
 * Google code
*/
$(document).ready(function(){
    var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
    $.getScript(gaJsHost + "google-analytics.com/ga.js", function(){
        try {
          var pageTracker = _gat._getTracker("UA-7607492-6");
            pageTracker._trackPageview();            
        } catch(err) {}
        
        // Track downloads, http and mailto's
        var filetypes = /\.(zip|exe|pdf|ppt|doc*|xls*|ppt*|mp3)$/i;
        $('a').each(function(){
            var href = $(this).attr('href');
			
			
			if (href != undefined) {
			
		            if ((href.match(/^https?\:/i)) && (!href.match(document.domain))){
		                $(this).click(function() {
		                    var extLink = href.replace(/^https?\:\/\//i, '');
		                    var extLink = 'ext:' + extLink;
		                    pageTracker._trackPageview(extLink);
		                });
		            }
		            else if (href.match(/^mailto\:/i)){
		                $(this).click(function() {
		                    var mailLink = href;
		                    pageTracker._trackPageview(mailLink);
		                });
		            }
		            else if (href.match(filetypes)){
		                $(this).click(function(event) {
		                    var source = event.target.href;
		                    pageTracker._trackPageview(source);
		                });
		            }
			
			}
			
        });
    });
});

/* on-click-message */
$(document).ready(function () {
    //$.cookie('cookie_do_not_display', '', { expires: -1 }); // delete cookie
    var $cookie_do_not_display = $.cookie('cookie_do_not_display');
	
	if (!$cookie_do_not_display) {
		$('#announcement').show();
    } else {
		$('#announcement').hide();
	}
	
	$('div#announcement img').click(function(){
											 
 		var date = new Date();
		date.setTime(date.getTime() + (24 * 60 * 60 * 1000));
		$.cookie('cookie_do_not_display', 'Do not display', {expires:date});
		//$.cookie('cookie_do_not_display', 'Do not display', {expires:365});
		$('div#announcement').slideUp();
		return false;
	});	
	
});

		$(document).ready(function(){
			$("#featured").tabs({fx:{opacity: "toggle"}}).tabs("rotate", 5000, true);
			$(".tabs").tabs();
			// Organisations nav
			$("li.about-nav").addClass("about-nav-arrow");					   
			$("li.about-nav a").after
				('<div id="subnav-wrapper"><div id="subnav"><div class="subnav-row"><ul><li><a href="/about/administrative-justice-and-tribunals-council/index.htm">Administrative Justice and Tribunals Council</a></li><li><a href="/about/cfo/index.htm">Court Funds Office</a></li><li><a href="/about/cmr.htm">Claims Management Regulation</a></li><li><a href="/about/criminal-injuries-compensation-authority/index.htm">Criminal Injuries Compensation Authority</a></li><li><a href="/about/criminal-cases-review-commission.htm">Criminal Cases Review Commission</a></li><li><a href="/about/cbr/index.htm">Commission on a Bill of Rights</a></li><li><a href="/about/hmcts/index.htm">HM Courts & Tribunals Service</a></li><li><a href="/about/hmi-prisons/index.htm">HM Inspectorate of Prisons</a></li><li><a href="/about/hmi-probation/index.htm">HM Inspectorate of Probation</a></li><li><a href="/about/hmps/index.htm">HM Prison Service</a></li></ul></div><div class="subnav-row"><ul><li><a href="/about/imb.htm">Independent Monitoring Board</a></li><li><a href="/about/jaco.htm">Judicial Appointments and Conduct Ombudsman</a></li><li><a href="/about/law-comm.htm">Law Commission</a></li><li><a href="/about/lsb/index.htm">Legal Services Board</a></li><li><a href="/about/lsc/index.htm">Legal Services Commission</a></li><li><a href="/about/lscp/index.htm">Legal Services Consumer Panel</a></li><li><a href="/about/lsrc/index.htm">Legal Services Research Centre</a></li><li><a href="/about/lo/index.htm">Legal Ombudsman</a></li><li><a href="/about/moj/index.htm">Ministry of Justice</a></li><li><a href="/about/noms/index.htm">National Offender Management Service</a></li></ul></div><div class="subnav-row-last"><ul><li><a href="/about/opg.htm">Office of the Public Guardian</a></li><li><a href="/about/ospt.htm">Official Solicitor and Public Trustee</a></li><li><a href="/about/parole-board/index.htm">Parole Board</a></li><li><a href="/about/ppo/index.htm">Prisons and Probation Ombudsman</a></li><li><a href="/about/probation.htm">Probation Service</a></li><li><a href="/about/vc/index.htm">Victims Commissioner</a></li><li><a href="/about/yjb/index.htm">Youth Justice Board</a></li><li class="more"><a href="/about/index.htm">All organisations...</a></li></ul></div></div></div>');					   
			$("#subnav-wrapper").mouseover(function(){
				$(".about-nav").addClass("about-nav-sub");				
			});
			$("#subnav-wrapper").mouseout(function(){
				$(".about-nav").removeClass("about-nav-sub");				
			});

			
		//COURTS
			$.ajax({
				url: "./?a=34127",
				dataType: "xml",
				success: function( xmlResponse ) {
					var data = $( "crt", xmlResponse ).map(function() {
						return {
								value: $( "cname", this ).text(),
								id: $( "id", this ).text()
								};
						}).get();
						
						$( "#courtcomplete" ).autocomplete({
							source: data,
							minLength: 0,
							select: function(event,ui){
								$('#court_id').val(ui.item.id);
							}
						});					
					}
				});
				$("#court-search").bind( "click", function(event) {
					event.preventDefault();
					$court_id = $('#court_id').val();
					$court_url = "";
					$court_reg = $('#court_region_name').val();
					
						if ($court_id) {
							$court_url = "courts_submitter.html?id=" + $court_id;
							$(this).colorbox({href:$court_url, width:"80%", height:"80%", iframe:true});
						} else {
							if ($court_reg) {
								$court_url = "courts_submitter.html?reg=" + $court_reg;
								$(this).colorbox({href:$court_url, width:"80%", height:"80%", iframe:true});
							}
						}
						
						if ($court_url) {
						} else {
							$("#crt_msg").show();
						}
		
				})
			
			//PRISONS
				$.ajax({
					url: "prisons.xml",
					dataType: "xml",
					success: function( xmlResponse ) {
						var data = $( "prs", xmlResponse ).map(function() {
							return {
									value: $( "name", this ).text(),
									url: $( "url", this ).text()
									};
							}).get();
							
							$( "#prisoncomplete" ).autocomplete({
								source: data,
								minLength: 0,
								select: function(event,ui){
									$('#prison_path').val(ui.item.url);
								}
							});					
						}
					});
					
					$("#prison-search").bind( "click", function(event) {
						event.preventDefault();
						$prison_url = "";
						$prison_url = $('#prison_path').val();
							if ($prison_url) {
								//this line can be deleted when live
								$prison_url = "http://www.justice.gov.uk/global/contacts/noms/prison-finder" + $prison_url;
								document.location = $prison_url;
							}
							if ($prison_url) {
						} else {
							$("#prison_msg").show();
						}
					})
					
			//TRIBUNALS
				$("#tribs-search").bind( "click", function(event) {
					event.preventDefault();
					$tribs_url = "";
					$tribs_url = $("#tribs_selector").val();
						if ($tribs_url) {
							//this line can be deleted when live
							$tribs_url = "http://www.justice.gov.uk" + $tribs_url;
							document.location = $tribs_url;
							//$(this).colorbox({href:$tribs_url, width:"80%", height:"80%", iframe:true});
						}
						if ($tribs_url) {
						} else {
							$("#tribs_msg").show();
						}
				})
				//FORMS
				$('#all-forms').submit(function(event) {
					event.preventDefault();
					$forms_url = "";
					$forms_url = $("#form_term").val();
						if ($forms_url) {
							$forms_url = "http://sitesearch.justice.gov.uk.openobjects.com/kb5/justice/justice/results.page?ha=forms&qt=" + $forms_url;
							document.location = $forms_url;
							//$(this).colorbox({href:$forms_url, width:"80%", height:"80%", iframe:true});
						}
						if ($forms_url) {
						} else {
							$("#general_form_msg").show();
						}
				})
				
				
			//COURT FORM
				$("#form-search").bind( "click", function(event) {
					event.preventDefault();
					$form_url = ""
					$form_num = $("#left").val();
					$form_title = $("#right").val();
					$form_cat = $("#court_forms_category").val();
					
					if ($form_num) {
						$form_url = "forms_submitter.html?num=" + $form_num;
						$(this).colorbox({href:$form_url, width:"80%", height:"80%", iframe:true});
					} else {
						if ($form_title) {
							$form_url = "forms_submitter.html?t=" + $form_title;
							$(this).colorbox({href:$form_url, width:"80%", height:"80%", iframe:true});
						} else {
							if ($form_cat) {
								$form_url = "forms_submitter.html?cat=" + $form_cat;
								$(this).colorbox({href:$form_url, width:"80%", height:"80%", iframe:true});
							}
						}
					}
					if ($form_url) {
						} else {
							$("#form_msg").show();
						}
				})
		});