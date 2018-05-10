$(function() {
	$('.fisdap-tabs-content div.fisdap-tab').hide();
	$('.fisdap-tabs-content div.fisdap-tab:first').show();
	$('.fisdap-tab-headings div:first').addClass('active');
	 
	$('.fisdap-tab-headings div a').click(function(){
		$('.fisdap-tab-headings div').removeClass('active');
		$(this).parent().addClass('active');
		var currentTab = $(this).attr('href');
		$('.fisdap-tabs-content div.fisdap-tab').hide();
		$(currentTab).show();
		return false;
	});
});