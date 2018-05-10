$(function(){
	setListStyles();
});

function setListStyles() {
	$("#sites-table").tablesorter();
	
	// style for firefox
	if ($.browser.mozilla){
        $("#titles").css({"padding-bottom": "5px", "top": "-27px"});
    }
}
