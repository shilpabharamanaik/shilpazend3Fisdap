$(function() {

var sizeLink = $("<a href='#'>See Sizing Chart</a>");


$("#JerseySize_div span.ridingjersey").append(sizeLink);
sizeLink.click(function(e) {
	e.preventDefault();
	$("#sizingModal").dialog("open");
	return false;
});

$("#bikeride-liability .pdfLink").button().click(function(e){	
	e.preventDefault();
	createPdf($("#releaseForm"), "bike-ride", "bikeride-liability");
})

$("#sizingModal").dialog({ autoOpen: false, width: 950, height: 300 , modal: true });

});