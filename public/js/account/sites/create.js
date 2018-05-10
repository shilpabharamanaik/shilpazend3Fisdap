$(function(){
	
});

function processSiteSave(response) {
	if (response) {
		window.location = "/account/sites/edit/siteId/" + response;
	}
}

function processSiteCancel() {
	window.location = "/account/sites/add";	
}