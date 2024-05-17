// Icon jquery, removes list style from any ul with documents

$().ready(function() {
	$("a[href $='.pdf']").closest('ul').addClass('normal-list-style')
	$("a[href $='.doc']").closest('ul').addClass('normal-list-style')
	$("a[href $='.dot']").closest('ul').addClass('normal-list-style')
	$("a[href $='.ppt']").closest('ul').addClass('normal-list-style')
	$("a[href $='.xls']").closest('ul').addClass('normal-list-style')
	$("a[href $='.docx']").closest('ul').addClass('normal-list-style')
	$("a[href $='.dotx']").closest('ul').addClass('normal-list-style')
	$("a[href $='.pptx']").closest('ul').addClass('normal-list-style')
	$("a[href $='.xlsx']").closest('ul').addClass('normal-list-style')
});