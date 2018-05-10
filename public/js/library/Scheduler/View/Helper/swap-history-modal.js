$(function(){
    // turn the history modal div into a modal
    $("#history-modal").dialog({
	modal: true,
	autoOpen: false,
	resizable: false,
	width: 700,
	title: "Swap history"
    });
});


function initSwapHistoryModal() {
    $("#historyCloseButton").button().blur();

    $("#historyCloseButton").click(function(e){
        e.preventDefault();
        $(this).unbind();
        $("#history-modal").dialog('close');
    });
}


