function initBulkReqEditModal(type) {
	$("#cancel-btn-"+type).button().blur();
	$("#save-btn-"+type).button().blur();
	
	// view/hide requirement list
	$("#show-reqs-"+type).click(function(e) {
		e.preventDefault();
		var reqList = $("#req-list-"+type);
		if (reqList.is(":visible")) {
			reqList.slideUp();
			$(this).html("see list");
		} else {
			reqList.slideDown();
			$(this).html("hide list");
		}
	});
}