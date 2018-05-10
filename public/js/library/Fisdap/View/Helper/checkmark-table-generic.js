$(document).ready(function() {
	initCheckmarkTable();
});

var selectRow = function(e) {
    e.preventDefault();

    // get parent TR
    var row = $(this).parent();

    // figure out which row we're working with
    var rowValue = row.attr("data-rowvalue");

    // we just assume that there is only one INPUT with class 'selected-row' in this view helper's container
    // and use that, instead of an explicit name property (cuz INPUT name can change!)
    var container = $(this).parents('.fisdap-table-scrolling-container.checkmark-table');

    // select this row in single mode
    if ($("input[name='select-mode']", container).val() == "single") {
        // deselect all rows
        row.siblings().removeClass("selected");
        // select this row
        row.addClass("selected");
    } else if ($("input[name='select-mode']", container).val() == "multiple") {
        if (row.hasClass('selected')) {
            // deselect this row
            row.removeClass("selected");
        } else {
            // select this row
            row.addClass("selected");
        }
    }
    updateSelected(container);
};

function initCheckmarkTable() {
	$("input[name=table_search]").focus(function(){$(this).addClass("fancy-input-focus");});
    $("input[name=table_search]").blur(function(){$(this).removeClass("fancy-input-focus");});

	// set up each table
	$('.fisdap-table-scrolling-container.checkmark-table').each(function(i, elem) {
		// set the selected row(s) for each table based on the hidden input
		var selectedRowVal = $(".selected-row", elem).val();
		var selectedRows = selectedRowVal.split(',');

		if (selectedRowVal != '') {
			$("tr").each(function() {
				if (selectedRows.indexOf($(this).attr("data-rowvalue")) > -1) {
					$(this).addClass("selected");
				}
			});
		}

		// update the count
		$(this).siblings(".multi-select-tools").find("span.num-selected").html(selectedRows.length);
	});
	
	// SEARCH THE ROWS
	$("input[name=table_search]").keyup(function(e) {
		e.preventDefault();
		
		var searchTerm = $(this).val().toLowerCase();
		var container = $(this).parent().siblings('.fisdap-table-scrolling-container.checkmark-table');
        container.find("div.null-msg").remove();
		
		container.find('tr').each(function(){
			if ($(this).html().toLowerCase().indexOf(searchTerm) >= 0) {
				$(this).show();
			} else {
				$(this).hide();
			}
		});

		if (container.find('tr:visible').length == 0) {
			container.prepend("<div class='null-msg'>No options match your search.</div>");
		}
    });

	
	// SELECT A ROW
	$(".fisdap-table-scrolling-container.checkmark-table tbody td").click(selectRow);
	
	// SELECT ALL/NONE
	$("a.select-aller").click(function(e) {
		e.preventDefault();
		var container = $(this).parent().siblings('.fisdap-table-scrolling-container.checkmark-table');
		var select = $(this).attr("data-mode") == 'all';
		
		container.find('tr').each(function(){
			if (select && !$(this).hasClass('selected')) {
				// select this row
				$(this).addClass("selected");
			}
			
			if (!select && $(this).hasClass('selected')) {
				// deselect this row
				$(this).removeClass("selected");
			}
		});
		
		updateSelected(container);
		
	});

}

function updateSelected(container) {
    var input = $("input.selected-row", container);

    // update hidden input
    var inputValue = new Array();
    container.find("tr.selected").each(function(){
        inputValue.push($(this).attr("data-rowvalue"));
    });
    input.val(inputValue);

    // update the count
    container.siblings(".multi-select-tools").find("span.num-selected").html(inputValue.length);
}
