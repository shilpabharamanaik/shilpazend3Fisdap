$(document).ready(function() {
	// are we using preceptor sign off report?
	if ($("#preceptor_signoff_report_options_form").length > 0) {
		return;
	}
	
	
	initializeEurekaReportOptionsForm();
});

/*-------------------------------------------------------------------------------------------------------------------------------------------
 *    Initializes the form elements for the eureka report options form
 *------------------------------------------------------------------------------------------------------------------------------------------*/
var initializeEurekaReportOptionsForm = function()
{
	
	$("#eureka_skills").change(function(){
		var show_combine = false;
		var current_val = $(this).val();
		
		if (current_val) {
			if (current_val.length > 1) {
				show_combine = true;
			}
		}
		
		if (show_combine) {
			$("#combine_erueka_graphs_wrapper").slideDown();
		}
		else {
			$("#combine_erueka_graphs_wrapper").slideUp();
		}
		
	});
	
	
	// init slider checkbox
	$("#eureka_combine_graphs").sliderCheckbox({onText: 'On', offText: 'Off'});
	
	// init the help bubble
	if ($("#eureka_point_elements").length > 0) {
		$('#eureka_point_eldsfements').cluetip({activation: 'click',
								local:true, 
								cursor: 'pointer',
								width: 680,
								cluezIndex: 2000000,
								cluetipClass: 'jtip',
								sticky: true,
								closePosition: 'title',
								closeText: '<img width=\"25\" height=\"25\" src=\"/images/icons/delete.png\" alt=\"close\" />'});
	}
	
	// when the goal/window change, validate it and change the success rate
	$("#eureka_goal").change(function(){validateEurekaPoint(true);updateEurekaSuccsesRatePrecentage();});
	$("#eureka_window").change(function(){validateEurekaPoint(false);updateEurekaSuccsesRatePrecentage();});
	
	$("#eureka_skills").trigger("change");
	$("#eureka_window").trigger("change");
	
} // end initializeEurekaReportOptionsForm()

/*-------------------------------------------------------------------------------------------------------------------------------------------
 *    Initializes the Eureka graphs.
 *    This is what actually calls our Eureka Graph plugin.
 *    This function is triggered by the initDataTables function in display.js
 *------------------------------------------------------------------------------------------------------------------------------------------*/
var initEurekaGraphsForEurekaReport = function()
{
	// are we using preceptor sign off report?
	if ($("#preceptor_signoff_report_options_form").length > 0) {
		initEurekaGraphsForPreceptorSignoffReport();
		return;
	}
	
	// we have to do a bit of work to ensure we can print this report
	initEurekaPrinting();
	
	// be patient. make sure everything has been appended before we begin work
	setTimeout(function(){
		
		// once the report has been run, let's generate the graphs
		generateEurekaGraphs();
		turnEurekaCanvasToImage();
		
		// do some minor style tweaks for our fixed table headers
		$(".eureka_table_wrapper").each(function(){
			if ($(this).find(".eureka_attempts_table_wrapper").css("height") == "550px") {
				// there's an overflow auto, do some minor header adjustments
				$(this).find(".fixed_eureka_attempts_thead").find(".eureka_shift_info_cell").css("width", "180px");
			}
		});

	},100);
	
	// give everybody a full second to initialize the eurkea graphs, then remove the throbbers and fade in the graphs
	setTimeout(function(){
		$(".loading_eureka_graph_throbber").remove();
		$(".graph_wrapper").animate({opacity:1});
	},1000);
	
} // end initEurekaGraphsForEurekaReport()

/*-------------------------------------------------------------------------------------------------------------------------------------------
 *    Prep the report for printing/compatibility. Turn each canvas into an image using Data URIs
 *------------------------------------------------------------------------------------------------------------------------------------------*/
var turnEurekaCanvasToImage = function()
{
	var canvas_ids = getEurekaCanvasIds();

	// now that we have our canvas_ids we can turn each into an image
	$.each(canvas_ids, function(i, v){
		
		var canvas = document.getElementById(v);
		var jpgURI = canvas.toDataURL();
		
		// to position this images appropriately we'll need to take
		// the first 2 for each graph and give them a special class
		var zero_styling = "";
		var extra_class = "";
		if (v.indexOf("zero_zero_flag") != -1) {
			zero_styling = "style='top:0em;left:0em;'";
			extra_class = "zero_zero_flag";
		}
		
		$("#" + v).parent().append("<img " + zero_styling + " class='canvas_to_img " + extra_class + "' src='" + jpgURI + "'>");
		$("#" + v).remove();
		
	});
	
	// since our positinging can get funny with these converted images, we'll need to adjust positioning based on y-axis
	adjustEurekaImagePositioning();

} //  end turnEurekaCanvasToImage()

/*-------------------------------------------------------------------------------------------------------------------------------------------
 *    Since our positinging can get funny with these  canvas to img converted images, we'll need to adjust
 *    positioning based on the eureka graph y-axis
 *------------------------------------------------------------------------------------------------------------------------------------------*/
var adjustEurekaImagePositioning = function()
{
	
	$(".jqplot-yaxis").each(function(){
		
		var axis_width = $(this).width();
		var left_offset = axis_width+11;
		
		$(this).parent().find(".canvas_to_img").each(function(){
			if (!$(this).hasClass("zero_zero_flag")) {
				$(this).css("left", left_offset + "px");
			}
		});
	});
	
} // end adjustEurekaImagePositioning()

/*-------------------------------------------------------------------------------------------------------------------------------------------
 *    Steps through each canvas on the page and gives it a unique ID
 *    @returns Array canvas_ids the array of IDs that were generated
 *------------------------------------------------------------------------------------------------------------------------------------------*/
var getEurekaCanvasIds = function()
{
	var canvas_count = 0;
	var canvas_ids = [];
	
	// now make each graph an image instead of a canvas
	$(".eureka_home").each(function(){
		var graph_count = 0;
		$(this).find("canvas").each(function(){
			// to position this images appropriately we'll need to take
			// the first 2 for each graph and give them a special class
			var zero_zero_flag = "zero_zero_flag";
			if (graph_count > 1) {zero_zero_flag = "";}
			
			var id = "eureka_canvas_" + canvas_count + "-" + zero_zero_flag;
			$(this).attr("id", id);
			canvas_ids.push(id);
			canvas_count++;
			graph_count++;
		});
	});
	
	return canvas_ids;

} // end getEurekaCanvasIds()

/*-------------------------------------------------------------------------------------------------------------------------------------------
 *    Calls the jQuery eurekaGraph plugin to initialze each graph.
 *    Also does a bit of small, additional tweaks to make them work right for this report
 *------------------------------------------------------------------------------------------------------------------------------------------*/
var generateEurekaGraphs = function()
{
	$(".eureka_graph_wrapper").each(function(){
		$(this).find(".graph_wrapper").each(function(){
			$(this).eurekaGraph();
			
			// if they've reached eureka do some additional styling/work
			if ($(this).find(".details-wrapper").hasClass("eureka_reached")) {
				var eureka_attempt_number = $(this).find(".eureka_reached").attr("data-eurekaattemptnumber");
				var eureka_table_cell = $(this).parent().parent().find(".eureka_table_wrapper").find("td[data-attemptnumber='" + eureka_attempt_number + "']");
				var current_title = eureka_table_cell.attr("title");
				var shift_id = eureka_table_cell.attr("data-successfor");
				
				$(this).parent().parent().find(".eureka_shift_info_cell[data-shiftid='" + shift_id + "']").addClass("eureka_reached");
				eureka_table_cell.addClass("eureka_reached").attr("title", "Eurkea point reached! " + current_title);
			}
			
		});
	});
	
} // end generateEurekaGraphs()

/*-------------------------------------------------------------------------------------------------------------------------------------------
 *    Initializes the Eureka graphs/report for printing.
 *------------------------------------------------------------------------------------------------------------------------------------------*/
var initEurekaPrinting = function()
{
	// remove our CSV and PDF links. Replace with a print link
	$("#export-report-links").find(".csvLink").remove();
	//$("#export-report-links").find(".pdfLink").remove();
	//$("#export-report-links").find(".printLink").remove();
	//$("#export-report-links").append('<a href="#" class="printLink export-button"><img src="/images/icons/print.png">Print</a>');
	
	// set up the trigger for printing
	$(".printLink").button().click(function(e){
		e.preventDefault();
		
		// hide the button and throw a throbber up (things take a second and this behavior is expected for our users)
		var print_btn = $(this);
		print_btn.css("opacity", "0").parent().append("<img src='/images/throbber_small.gif' id='print_eureka_throbber' style='display:none;position:absolute;top:0.5em;right:2.75em;'>");
		$("#print_eureka_throbber").fadeIn();
		
		// we don't want to print the actual results since there is a bunch of conditional styling
		var print_ready_div =  setUpPrintReadyDiv();
		
		// do some extra styling if we are printing the preceptor signoff report
		if ($("#preceptor_signoff_report_options_form").length > 0) {
			var table_header_html = "";
			print_ready_div.find(".fixed_eureka_attempts_thead").each(function(){
				table_header_html = $(this).find("table").html();
				var newhtml = table_header_html.replace(/Rating/g, "");
				var newthead = $(this).parent();
				newthead.find(".eureka_attempts_table_wrapper").find("table").prepend(newhtml);
				newthead.prepend("<img src='/images/reports/table_header_bg.png' style='z-index:-1;position:absolute;top:0em;left:0em;'>");
				newthead.css("position", "relative");
				newthead.find("th").css("background", "transparent");
				newthead.prepend("<img src='/images/reports/table_header_bg.png' style='z-index:-1;position:absolute;top:0em;right:-2em;'>");
				$(this).remove();
			});
		}
		
		// replace eureka key lines with images
		replaceEurekaKeyCSSLinesWithImages(print_ready_div);
		
		// get clever/hacky for printing background images for the table header/summary box
		hackForPrintingEurekaBgImages(print_ready_div);
		
		if (!($("#preceptor_signoff_report_options_form").length > 0)) {
			// throw in a page break after each skill so we have one graph per page
			print_ready_div.find(".eureka_skill_wrapper").each(function(){$(this).after("<div style='page-break-after:always;'></div>");});
		}
		
		// give JS some time to finish styling before we actually try to print
		// then use our printArea() plugin
		setTimeout(function(){
			$('#print-eureka-report').printArea();
			print_ready_div.empty();
			
			setTimeout(function(){
				$("#print_eureka_throbber").remove();
				print_btn.css("opacity", "1");
			},200);
		},1000);
		
	}); // end printLink click trigger
	
} // end initEurekaPrinting()


var hackForPrintingEurekaBgImages = function(print_ready_div)
{
	// add a background that will show up for the details wrapper (this is the summary box)
	print_ready_div.find(".details-wrapper").each(function(){
		
		// make it look different if they have reached eureka
		var img_src = "/images/reports/not_met_eureka_bg.png";
		if ($(this).hasClass("eureka_reached")) {img_src = "/images/reports/met_eureka_bg.png";}
		
		// weird ass styling to make it work
		$(this).removeClass("eureka_reached");
		$(this).prepend("<img src='" + img_src + "' style='z-index:-1;position:absolute;top:0em;left:0em;'>");
		$(this).css("position", "relative");
		$(this).find(".eureka_custom_details_msg").css("position", "absolute").css("height", "40px").css("z-index", "100").css("background", "transparent");
		$(this).find(".details").css("position", "absolute").css("height", "65px").css("top", "30px").css("z-index", "100").css("background", "transparent");
		$(this).css("height", "104px").css("background", "transparent").css("margin-top", "1em");
		
		if ($("#preceptor_signoff_report_options_form").length > 0) {
			$(this).prepend("<img src='" + img_src + "' style='z-index:0;position:absolute;top:0em;right:0em;'>");
		}
	});
	
	// add a background that will show up for the table thead header
	print_ready_div.find(".fixed_eureka_attempts_thead").each(function(){
		// more werd ass styling to make it work
		$(this).prepend("<img src='/images/reports/table_header_bg.png' style='z-index:-1;position:absolute;top:0em;left:0em;'>");
		$(this).css("position", "relative");
		$(this).find("th").css("background", "transparent");
	});
	
} // end hackForPrintingEurekaBgImages()

var replaceEurekaKeyCSSLinesWithImages = function(print_ready_div)
{
	// first step through each of the lines
	print_ready_div.find(".key-line").each(function(){
		var img_name = "point";
		
		// figure outwhat the image name is based on the line's class
		if ($(this).hasClass("red")) {img_name = "red_line";}
		else if ($(this).hasClass("green")) {img_name = "green_line";}
		else if ($(this).hasClass("yellow")) {img_name = "yellow_line";}
		else if ($(this).hasClass("gray")) {img_name = "gray_line";}
		
		// append the image (this will appear replace hte line completey since border colors do not print)
		$(this).append("<img style='float:left;margin-right:1em;margin-top:0.5em;' src='/images/reports/eureka_key_" + img_name + ".png'>");
		$(this).parent().css("margin-right", "0.5em");
	})
	
	// now go through each point and do the same thing
	print_ready_div.find(".key-green-point").each(function(){
		$(this).append("<img style='float:left;margin-left:0.65em;margin-right:1.55em;margin-top:0.1em;' src='/images/reports/eureka_key_point.png'>");
		$(this).parent().css("margin-right", "0.5em");
	});
	
} // end replaceEurekaKeyCSSLinesWithImages()

var setUpPrintReadyDiv = function()
{
	$('body').append("<div id='print-eureka-report'></div>");
	var print_ready_div = $("#print-eureka-report");
	
	$(".eureka_student_report_wrapper").each(function(){
		print_ready_div.append($(this).html());
		
		if ($("#preceptor_signoff_report_options_form").length > 0) {
			print_ready_div.append("<div style='page-break-after:always;'></div>");
		}
	});
	
	print_ready_div.find(".success-list").remove();
	print_ready_div.find(".date-list").remove();
	print_ready_div.find(".print-eureka").remove();
	return print_ready_div;
}

var updateEurekaSuccsesRatePrecentage = function()
{
	var window_val = parseInt($("#eureka_window").val());
	var goal_val = parseInt($("#eureka_goal").val());
	var rate = (goal_val / window_val)*100;
	var success_rate = Math.round(rate * 100) / 100;
	
	$("#eureka_success_rate_precentage").text(success_rate);
	$("#eureka_success_precentage_wrapper").effect("highlight");
	
}

var validateEurekaPoint = function(goal_changed)
{
	var window_val = parseInt($("#eureka_window").val());
	var goal_val = parseInt($("#eureka_goal").val());
	
	// first make sure both are integers
	if (isNaN(window_val)) {
		$("#eureka_window").val("20");
	}
	
	if (isNaN(goal_val)) {
		$("#eureka_goal").val("16");
	}
	
	window_val = parseInt($("#eureka_window").val());
	goal_val = parseInt($("#eureka_goal").val());
	
	// now, make sure that the window is great than or equal to the window
	if (window_val < goal_val) {
		
		if (goal_changed) {
			// we're changing the goal, make the window move
			$("#eureka_window").val(goal_val);
		}
		else {
			// we're changing the window, make the goal move
			$("#eureka_goal").val(window_val);
		}
	}
	
}