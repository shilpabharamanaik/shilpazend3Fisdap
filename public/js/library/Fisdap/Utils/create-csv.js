function createCsv(tables, fileName, divId){
	// remove any old forms we've hanging around
	$("#csvGenerate").remove();
	
	if(fileName == undefined){
		fileName = 'report.csv';
	}
    
	// append .csv to filename if not found in filename
    if(fileName.indexOf('.csv') === -1) {
      	fileName = fileName + '.csv';
    }

	// We need to do some cleaning up of the incoming tables...
	var fileContents = "";
	$.each(tables, function(tableTitle, table) {
		// remove anything that's explicitly hidden
		table.find('.hidden').remove();
		fileContents += tableToString(table, tableTitle) + '\r\n';
	});
	//console.log(fileContents);
	
	// let's try creating a little form to submit
	var newForm = $("<form id='csvGenerate' method='post' action='/csv/create-csv' class='hidden'>"+
					"<input type='hidden' name='fileName' value='" + escape(fileName) + "' />"+
					"<input type='hidden' name='fileContents' value='" + escape(fileContents) + "' />"+
					"</form>");
	
	$('#'+divId+' .csvLink').find('form').remove();
	$('#'+divId+' .csvLink').append(newForm);
	$('#csvGenerate').submit();
	return true;
}

function tableToString(table, tableTitle) {
	var string = '"' + tableTitle + '"\r\n';
	
	// format the header if there is one
	if ($(table).find('thead').length > 0) {
		$(table).find('thead').each(function(index, item) {
		    string += formatTableHeader($(item));
		});
	}
	
	// grab the rest of the rows
	$(table).find('tr').has('td').each(function() {
	    $('td', $(this)).each(function(index, item) {
	        string += '"' + formatTableCell($(item)) + '",';
	    });
		string += '\r\n';
	});

	return string;
}

function formatTableHeader(header) {
    var grid = createTableGrid(header);

    return gridToString(grid);
}

function formatTableCell(cell) {
	// add a newline at the end of all the appropriate tags
    $(cell).html($(cell).html().replace("<br>", " "));

	$('h4', $(cell)).each(function(index, el) {
	    $(el).text($(el).text() + "\n");
	});
	$('div', $(cell)).each(function(index, el) {
	    $(el).text($(el).text() + "\n\n");
	});
	
	return $(cell).text();
}

function createTableGrid(table) {
    var grid = [];

    // loop through the rows to create an array for each
    $(table).find('tr').each(function(row_index) {
        grid[row_index] = [];
    });

    // loop through the rows to create a grid more suitable for csv export
    $(table).find('tr').each(function(row_index, row) {
        var col_index = 0;
        $(row).find("th").each(function() {
            // format the contents of the cell
            var formatted_contents = formatTableCell($(this));

            // get to the next unassigned cell
            while (typeof grid[row_index][col_index] != "undefined") {
                col_index++;
            }

            // add this cell to the next empty cell in the grid
            grid[row_index][col_index] = formatted_contents;

            // if this cell spans more than one row, add the necessary blank cells to subsequent rows
            if ($(this).attr("rowspan") > 1) {
                for (var ri = 1; ri < $(this).attr("rowspan"); ri++ ) {
                    grid[row_index+ri][col_index] = "";
                }
            }

            // if this cell spans more than one column, add the necessary subsequent cells
            if ($(this).attr("colspan") > 1) {
                for (var ci = 1; ci < $(this).attr("colspan"); ci++ ) {
                    col_index++;
                    grid[row_index][col_index] = "";
                }
            }

            col_index++;
        });
    });

    return grid;
}

function gridToString(array) {
    var string = '';

    // loop through each row
    $(array).each(function() {
        // loop through each cell and format it for csv
        $(this).each(function(j, cell) {
            string += '"' + cell + '",';
        });
        string += '\r\n';
    });

    return string;
}