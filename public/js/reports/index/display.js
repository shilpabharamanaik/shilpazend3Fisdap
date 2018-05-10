function reportsBrokenRobot(report_values, errorResponseText) {
    var broken_msg = "<div class='report_error_buttons'><span class='extra-small gray-buttons'><a id='refresh_reports' href='#'>Refresh page</a></span>";
    broken_msg += "<span class='extra-small green-buttons'><a id='reports_report_error_btn' href='#'>Report bug</a></span></div>";
    brokenRobot(false, broken_msg);

    if (typeof(report_values.push) != 'undefined') {
        report_values.push({name: "error_response", value: errorResponseText});
    }

    $("#reports_report_error_btn").button().blur().click(function (e) {
        e.preventDefault();
        var this_btn = $(this);
        hideBrokenRobotButtons($(this));
        $.post("/reports/index/report-display-error", report_values,
            function (data) {
                $("#refresh_reports").css("opacity", "1");
                $("#loading_broken_btn_action").remove();
                this_btn.parent().append("<img id='green_report_bug_checkmark' style='position:absolute;right:9.5em;bottom:3.5em;width:20px;' src='/images/icons/scenario-valid.png'>");
                $(".robot-refresh-txt").prepend("Thanks! We have received your bug report.");

            }, "json");
    });

    $("#refresh_reports").button().blur().click(function (e) {
        e.preventDefault();
        $("#green_report_bug_checkmark").remove();
        hideBrokenRobotButtons($(this));
        document.location.reload();
    });
}

function reportCachePoller(configId, values) {
	if($("#New").length > 0){
		$("#New").remove();
	}
    (function poll(delay) {
        if (typeof(delay) == 'undefined') {
            delay = 1000;
        }
        setTimeout(function () {
            $.ajax({
                url: "/reports/index/load-cached-report/config_id/" + configId,
                success: function (data) {
                    // did we get the data we want?
                    if (data.waiting) {
                        //Setup the next poll recursively, 5 second intervals
                        poll(5000);
                    } else if (data.isError) {
                        //$('#report-waiting').html('An error occurred! ' + data.message);
                        reportsBrokenRobot(values, data.message);
                    } else {
                        displayReport(data);
                    }
                },
                dataType: "json"
            });
        }, delay);
    })();
}
$(document).ready(function() {
    // define a container for any report-specific JS callbacks
    // to be called when the report results are loaded
    window.reportResultsCallbacks = [];

    // nav bar gets custom styling on this page
    styleNavBar();
    if ($.browser.chrome) {
        $("#goalset-table .goalset-buttons").css("top", "-3.3em");

        if($("h1").first().text().indexOf("Graduation Requ") != -1){
            $("#goalset-table .goalset-buttons").css("top", "-2.6em");
        }

	}
	
	// initialize the export buttons
	initExportButtons();
	
	// turn chosens into chosens
	$(".chzn-select").chosen();
	
    // if we already have tables in report-results, then DataTables-ify them
    initDataTables();
	
	// if there are tabbed tables, then tab-ify them
    initTabs();

    // other js stuff for the report as a whole
    initReport();
	
	// turn buttons into buttons
	$("#hide-report-form").button();
	$("#go-button").button();
	
	// if we have report contents, hide the form
	if ($("#report-content").is(":visible")) {
		$("#report-form").hide();
	} else {
		// visibility for the form hide link should match visibility for the report contents
		$("#hide-report-form").hide();
	}


    window.displayReport = function(data) {
        // we have existing airway management data we need to remove/clean up
        if ($(".am_eureka_dialog").length > 0) {
            $(".am_eureka_dialog").remove();
        }

        // display HTML output in container
        $("#report-content").html(data.html);

        // show the legible report info card
        $("#report-summary").html(data.summary);

        // give the page a second to load all of the content
        // before attempting animations (gets a little glitchy with lots of content)
        // decide how long this should be based on the length of the returned content
        var timeout_length = parseInt(data.html.length)/30;
        // but don't make them wait longer than 2 seconds. At this point, jQuery kind of gives up anyways and content will flash (but not as awkwardly)
        if (timeout_length > 2000) {timeout_length = 2000;}

        setTimeout(function(){
            $("#report-info").slideDown();
            //$("#report-form").slideUp();
            $("#hide-report-form").show();
            $("#report-content").fadeIn();
            removeThrobberShowButtons();

            // update the nav bar, if necessary
            if ($("#display-report-nav-bar").length > 0) {
                $("#display-report-nav-bar").html(data.navBar);
                styleNavBar();
                initNavBar();
            }

            // initialize the new export buttons
            initExportButtons();

            // dataTables-ize the tables in the content
            initDataTables();

            // if there are tabbed tables, then tab-ify them
            initTabs();
            
            // other js stuff for the report as a whole
                    initReport();


            // Run any report-specific JS that operates on the report results DOM elements
            // essentially a callback that a report can implement in its JS
            // just call window.reportResultsCallbacks.push(function() {}); in your report's JS
            if (typeof(window.reportResultsCallbacks) != 'undefined' && window.reportResultsCallbacks.length > 0)
            {
                $.each(window.reportResultsCallbacks, function(i, method) {
                    if (typeof(method) == 'function') {
                        method();
                    }
                });
            }

            // initialize the eureka modal, if there is one
            // @todo this really should be converted to a callback, see comments immediately above
            if ($("#eureka-modal").length > 0) {
                initEurekaModal();
            }

            // make sure the waiting for report element is hidden
            $('#report-waiting').hide();
        },timeout_length);
    }


	// RUN THE REPORT
    $("#report-form-form").submit(function(event) {
        event.preventDefault();

        // add throbber
        //$("#report-form-form").append("<img id='submit-report-throbber' src='/images/throbber_small.gif'>");
        $("#go-button").button("disable").css("opacity", "0");
        $("#hide-report-form").hide();

        // show waiting message and hide form while processing
        $('#report-waiting').show();
        $("#report-form").slideUp();

        // remove old errors
        $('#report-form-errors').slideUp();
        $(".input-error").removeClass('input-error');

        // get the form values as string
        var values = $(this).serializeArray();

        // submit to AJAX and register callback for results
        $.post($(this).attr("action"), values,
            function (data) {
                if (data.isError) {
                    // hide the waiting message and show form again
                    $('#report-waiting').hide();
                    $("#report-form").slideDown();

                    // there were form errors, so re-display the form with those errors
                    var htmlErrors = '<ul>';
                    $.each(data.errors, function (elementId, msgs) {
                        if (elementId == "available-list") {
                            $("#available_list_chzn a.chzn-single").addClass('input-error');
                        } else {
                            $("#" + elementId).addClass('input-error');
                        }

                        $.each(msgs, function (key, msg) {
                            if (typeof msg == "object") {
                                $.each(msg, function (key, submsg) {
                                    htmlErrors += '<li>' + submsg + '</li>';
                                });
                            } else {
                                htmlErrors += '<li>' + msg + '</li>';
                            }
                        });
                    });
                    htmlErrors += '</ul>';

                    $('#report-form-errors').html(htmlErrors);
                    $('#report-form-errors').slideDown();
                    removeThrobberShowButtons();
                    $('html,body').animate({scrollTop: $('#report-form-errors').offset().top}, 'slow');

                } else {

                    // start polling the server to see when report is ready
                    // check at 1000ms first and then 5000ms
                    var configId = data.configId;
                    reportCachePoller(configId, values);

                }

            }, "json").fail(function (fail_data) {
                //console.log(fail_data['responseText']);
                // set up a link for sending a bug report
                var errorResponseText = fail_data['responseText'];
                reportsBrokenRobot(values, errorResponseText);
            });
    });
    
    // re-show the config form
    $("#show-report-form").click(function(event) {
        event.preventDefault();
        
        $("#report-form").slideDown();
		$("#report-info").slideUp();
    });
	
	// hide the config form and clear changes to the form
    $("#hide-report-form").click(function(event) {
        event.preventDefault();
        
        $("#report-form").slideUp();
		$("#report-info").slideDown();

    });

});

var hideBrokenRobotButtons = function(trigger){
	$("#refresh_reports").css("opacity", "0");
	$("#reports_report_error_btn").css("opacity", "0");
	trigger.parent().append("<img id='loading_broken_btn_action' src='/images/throbber_small.gif' style='display:none;position:absolute;width:16px;left:16em;bottom:3.5em;'>");
	$("#loading_broken_btn_action").fadeIn();
}

var removeThrobberShowButtons = function(){
	// remove throbber and show buttons
	$("#submit-report-throbber").remove();
	$("#go-button").button("enable").css("opacity", "1");
	$("#hide-report-form").show();
}

function initExportButtons() {
	makeExportButtons();
	
	//
	// PRINT THE REPORT
	//
	$('#export-report-links').on('click','.pdfLink',function(event){
		event.preventDefault();
		
		var title = $('h1:first-of-type').text();
		var pdfName = title + ".pdf";
		var header = $('#report-info').html();
		var reportContent = $('#report-content').html();
		var divContents = $("<div></div>");
		divContents.empty();
		divContents.html(header+reportContent);
		
		styleForPdf(divContents, title);

		createPdf(divContents, pdfName, "export-report-links");
		
		return false;
	});
	
	//
	// EXPORT THE REPORT
	//
	$('#export-report-links').on('click','.csvLink',function(event){
		event.preventDefault();
		
		var pageTitle = $('h1:first-of-type').text();
		var csvName = pageTitle.replace(new RegExp(" ", 'g'), "") + ".csv";
		
		var tables = {};
		// first add the summary to the tables
		tables["Report options"] = $("#report-summary");
		
		// then add the tables from the page
		$.each($('#report-results').find('table.dataTable'), function(tableId, table) {
			var title = $(table).attr('data-csvtitle');

			// if there wasn't a name specified for this table, use the parent header
			if (typeof title == "undefined") {
                title = $(table).parents('div.table-container').find("h3.table-title").text();
			}

			var tableClone = $(table).clone();
			
			// do a little formatting
			// if this is a no header table, remove the header
			if ($(tableClone).hasClass('hide-header')) {
				$(tableClone).find("thead").remove();
			}
			
			tables[title] = tableClone;
            //console.log(tables);
		});
		
		createCsv(tables, csvName, "export-report-links");
		
		return false;
	});
	
}


// Initiate DataTables library for any TABLE elements found in report-content
function initDataTables() {

    $("#report-content table:not(.noInitDataTable)").each(function(){
		
		if ($(this).hasClass("no_data_table")) {
			return;
		}
		
		// check to see if this table wants the search box
		search = !$(this).attr("data-noSearch");
		sort = !$(this).attr("data-noSort");
		info = !$(this).attr("data-noInfo");

       	var table = $(this).dataTable({
			"oLanguage": {
				"sEmptyTable": $(this).attr('data-nullmsg'),
				"sSearch": ""
			},
			"bJQueryUI" : true,
			"bPaginate": false,
			"scrollX": true,
			"bFilter": search,
			"bSort": sort,
			"bInfo": info,
			"fnFooterCallback": function() {
				updateFooter($(this));
			},
			"aoColumnDefs": [
				{ 
					"bSortable": false, 
					"aTargets": [ "no-sort-col" ] // <-- turns off column sorting for cols that match this class
				} 
			]
        });

		if($("#airway_management_report").length > 0) {
			updateAirwayManagementTotalsRow(true);
			initAirwayManagementEurekaGraphs();
		}

	});
	
	$("#report-content table").each(function(){
		// make sure the widths still match
		//var width = $(this).width() - 18;
		//$(this).parent().find("div.data-tables-footer").css("width", width);
    });
	
	
	// add keytag plugin to the search box
	$(".dataTables_filter input").attr('title', 'Type to search...').fieldtag().addClass("search-box");
	
	// deal with on page load eureka graphs
	if($(".eureka_report_wrapper").length > 0){
		initEurekaGraphsForEurekaReport();
	}

    // format any currency cells
    $("td.currency").each(function() {
        $(this).text(numeral($(this).text()).format("$0,0.00"));
    });

}

// Initiate tabs for any TABLE elements found in report-content
function initTabs() {
	$("#report-content .tabs").each(function(){
		$(this).tabs({
			"show": function(event, ui) {
			    var table = $.fn.dataTable.fnTables(true);
			    if ( table.length > 0 ) {
			        $(table).dataTable().fnAdjustColumnSizing();
			    }
			},
			"activate": function(event, ui) {
				var table = ui.newPanel.find("table.dataTable");
				//console.log(table);
				updateFooter(table);
			}
		});
	});
}

function styleNavBar() {
	// nav bar gets custom styling on this page
	$("#nav-bar").css({"margin-top": "1em"});
}

function initReport() {
    // logic for notice details toggler
    $(".report-notice a.toggleDetails").click(function(event) {
        event.preventDefault();
        var details = $(".report-notice .details");

        if ($(details).css("display") != "none") {
            $(details).slideUp();
        } else {
            $(details).slideDown();
        }
    });
}

function styleForPdf(divContents, title) {
	// add css files
	var htmlHead = $("head");
	htmlHead.append("<link href='/css/reports/index/pdf-display.css' type='text/css' rel='stylesheet' id='pdf-styles'>");
	
	// add title
	$(divContents).find("#report-summary").prepend("<h1>"+title+"</h1>");
	
	// get rid of superfluous text
	$(divContents).find("#report-info-header h3.section-header").remove();
	
	// get rid of superfluous buttons
	$(divContents).find("#export-report-links").remove();
	$(divContents).find("#show-report-form").remove();
	$(divContents).find("table.dataTable thead th div.DataTables_sort_wrapper span.DataTables_sort_icon").remove();
	
	// restyle the footers so you can't tell they're not wide enough
	$(divContents).find("div.data-tables-footer").css({"background": "none", "border-bottom": "none"});
	
	// get rid of data tables search input
	$(divContents).find(".dataTables_filter").remove();
	
	// if there aren't already page breaks,
	// loop through the tables and add page breaks whenever we start running out of room
    // ACTUALLY: it looks like wkhtmltopdf improved, and we no longer need to do this.
    // I'm leaving the code here in case some weirdness shows up in the future though
    // (it's happened before!) - Jesse M 3/22/15
    // see https://fisdap.atlassian.net/browse/MAIN-2064
    /**
	if ($(divContents).find("div.page-break").length < 1) {
		var rowCount = 0;
		$(divContents).find("table").each(function(){
			rowCount += $(this).find('tr').length;
			rowCount += 4; // add 1 each for the header and the footer, and 2 for the spacing
			
			// if we're getting too long, add a page break and restart the count
			if (rowCount >= 34) {
				$(this).parent().parent().prepend("<div style='page-break-after: always'></div>");
				rowCount = 0;
			}
		});
	}
     **/

}

function updateFooter(table) {
	// loop through each row of the footer and update it according to class	
	$(table).find("tfoot tr").each(function() {
		var updateFunction = $(this).attr('class') + "FootRow";
		if ( typeof window[updateFunction] == 'function' ) { 
		    window[updateFunction]($(this), table);
		}
	});
}

function averageFootRow(footRow, table) {
	// figure out which columns contain numerical data
	// by testing the classes of the cells in the first row
	var numericalCols = []
	$("tbody tr:visible:first td", table).each(function(i, element) {
		if ($(element).hasClass("noAverage") == false) {
			// This element does not have the class "noAverage"
			numericalCols.push(i + 1); // increment index to get base-1 numbering
		}
	});
		
	// I set this on a slight delay to give DataTables time to do the actual sorting
	setTimeout(function () {
		var rowCount = $(table).find('tbody tr:visible').length;
		
		// loop through and update each column that was identified as a numerical column
		$.each(numericalCols, function(i, colNumber) {
			var colSum = 0;
			$(table).find('tbody tr:visible').each(function() {
				var value = parseFloat($(this).find('td:nth-child('+colNumber+')').text());
				if (!isNaN(value)) {
					colSum += value;
				}
			});

			var footCell = $(footRow).find('td:nth-child('+colNumber+')');
			var average = Math.round(colSum*100/rowCount)/100;
			if ($(footCell).hasClass("percent")) {
				average = average.toString() + "%";
			}
			if (isNaN(colSum)) {
				average = "n/a";
			}
			$(footCell).text(average);
		});

		// make sure the footers of the tables match the widths of the tables
		//var width = $(table).width() - 18;
		//$(table).parent().find("div.data-tables-footer").css("width", width);
	
	}, 0);
}

/**
 * Utility function to provide a dynamically-recalculated footer row
 * that shows each column's sum
 */
function sumFootRow(footRow, table) {	
	// I set this on a slight delay to give DataTables time to do the actual sorting
	setTimeout(function () {		
		// figure out which columns contain numerical data
		// by testing the classes of the cells in the first row
		var numericalCols = []
		$("tbody tr:visible:first td", table).each(function(i, element) {
			if ($(element).hasClass("noSum") == false) {
				// This element does not have the class "noSum"
				numericalCols.push(i + 1); // increment index to get base-1 numbering
			}
		});
		
		// loop through and update each column that was identified as a numerical column
		$.each(numericalCols, function(i, colNumber) {
			var colSum = 0;
			$(table).find('tbody tr:visible').each(function() {
				var cell = $(this).find('td:nth-child('+colNumber+')');
                var value  = numeral().unformat(cell.text());

				if (!isNaN(value)) {
					colSum += value;
				}
			});

			var footCell = $(footRow).find('td:nth-child('+colNumber+')');
			if (isNaN(colSum)) {
				colSum = "n/a";
			}
            var sum = Math.round(colSum*100)/100;

            if ($(footCell).hasClass("currency")) {
                sum = numeral(sum).format('$0,0.00');
            }

			$(footCell).text(sum);
		});
		
		// make sure the footers of the tables match the widths of the tables
		//var width = $(table).width() - 18;
		//$(table).parent().find("div.data-tables-footer").css("width", width);
	
	}, 0);
}

/**
 * Utility function to provide a dynamically-recalculated footer row
 * that shows each column's min
 */
function minFootRow(footRow, table) {	
	// I set this on a slight delay to give DataTables time to do the actual sorting
	setTimeout(function () {		
		// figure out which columns contain numerical data
		// by testing the classes of the cells in the first row
		var numericalCols = []
		$("tbody tr:visible:first td", table).each(function(i, element) {
			if ($(element).hasClass("noMin") == false) {
				// This element does not have the class "noMin"
				numericalCols.push(i + 1); // increment index to get base-1 numbering
			}
		});
		
		// loop through and update each column that was identified as a numerical column
		$.each(numericalCols, function(i, colNumber) {
			possibleValues = new Array();
			$(table).find('tbody tr:visible').each(function() {
				var value = parseFloat($(this).find('td:nth-child('+colNumber+')').text());
				if (!isNaN(value)) {
					possibleValues.push(value);
				}
			});

			var footCell = $(footRow).find('td:nth-child('+colNumber+')');
			
			if (possibleValues.length) {
				minValue = Math.min.apply( Math, possibleValues );
			} else {
				minValue = "n/a";
			}
			$(footCell).text(minValue);
		});
		
		// make sure the footers of the tables match the widths of the tables
		//var width = $(table).width() - 18;
		//$(table).parent().find("div.data-tables-footer").css("width", width);
	
	}, 0);
}

/**
 * Utility function to provide a dynamically-recalculated footer row
 * that shows each column's max
 */
function maxFootRow(footRow, table) {	
	// I set this on a slight delay to give DataTables time to do the actual sorting
	setTimeout(function () {		
		// figure out which columns contain numerical data
		// by testing the classes of the cells in the first row
		var numericalCols = []
		$("tbody tr:visible:first td", table).each(function(i, element) {
			if ($(element).hasClass("noMax") == false) {
				// This element does not have the class "noSum"
				numericalCols.push(i + 1); // increment index to get base-1 numbering
			}
		});
		
		// loop through and update each column that was identified as a numerical column
		$.each(numericalCols, function(i, colNumber) {
			possibleValues = new Array();
			$(table).find('tbody tr:visible').each(function() {
				var value = parseFloat($(this).find('td:nth-child('+colNumber+')').text());
				if (!isNaN(value)) {
					possibleValues.push(value);
				}
			});

			var footCell = $(footRow).find('td:nth-child('+colNumber+')');
			
			if (possibleValues.length) {
				maxValue = Math.max.apply( Math, possibleValues );
			} else {
				maxValue = "n/a";
			}
			
			$(footCell).text(maxValue);
		});
		
		// make sure the footers of the tables match the widths of the tables
		//var width = $(table).width() - 18;
		//$(table).parent().find("div.data-tables-footer").css("width", width);
	
	}, 0);
}
