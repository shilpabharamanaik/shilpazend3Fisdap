$(document).ready(function() {
	initializePreceptorSignoffReportOptionsForm();
});

/*-------------------------------------------------------------------------------------------------------------------------------------------
 *    Initializes the form elements for the preceptor sign off report options form
 *------------------------------------------------------------------------------------------------------------------------------------------*/
var initializePreceptorSignoffReportOptionsForm = function()
{
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
	$("#eureka_window").trigger("change");
	
} // end initializePreceptorSignoffReportOptionsForm()

/*-------------------------------------------------------------------------------------------------------------------------------------------
 *    Initializes the Eureka graphs.
 *    This is what actually calls our Eureka Graph plugin.
 *    This function is triggered by the initDataTables function in display.js
 *------------------------------------------------------------------------------------------------------------------------------------------*/
var initEurekaGraphsForPreceptorSignoffReport = function()
{
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
		
		setTimeout(function(){
			// replace all of the "attempts" with "ratings"
			$(".details-wrapper").each(function(){
				oldhtml = $(this).html();
				var newhtml = oldhtml.replace(/attempt/g, "rating");
				newhtml = newhtml.replace(/Attempt/g, "Rating");
				$(this).html(newhtml);
			});
			
			$(".key-wrapper").each(function(){
				oldhtml = $(this).html();
				var newhtml = oldhtml.replace(/attempt/g, "rating");
				$(this).html(newhtml);
			});
			
			// now deal with highlighting eureka points in the table
			$(".graph_wrapper").each(function(){
				
				// if they've reached eureka do some additional styling/work
				if ($(this).find(".details-wrapper").hasClass("eureka_reached")) {
					
					var eureka_attempt_number = $(this).find(".eureka_reached").attr("data-eurekaattemptnumber");
					var eureka_table_cell = "";
					var attempt_count = 1;
					var table_wrapper = $(this).parent().parent().parent();
					var class_to_search_for = ".preceptor_rating_cell";
					
					if ($(this).parent().hasClass("student_signoff_eureka")) {
						class_to_search_for = ".student_rating_cell";
					}
					
					table_wrapper.find(class_to_search_for).each(function(){
						if (attempt_count == eureka_attempt_number) {
							eureka_table_cell = $(this);
						}
						attempt_count++;
					});
					
					eureka_table_cell.addClass("eureka_reached").attr("title", "Eurkea point reached!");
				}
				
			});
		
		},100);

	},100);
	
	// give everybody a full second to initialize the eurkea graphs, then remove the throbbers and fade in the graphs
	setTimeout(function(){
		$(".loading_eureka_graph_throbber").remove();
		$(".graph_wrapper").animate({opacity:1});
	},1000);
	
} // end initEurekaGraphsForPreceptorSignoffReport()