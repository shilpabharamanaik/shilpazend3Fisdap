function createPdf(divContents, pdfName, divId, useLegacyBinary){
	// remove any old forms we've got hanging around
	$("#pdfGenerate").remove();

    if (useLegacyBinary == undefined) {
        useLegacyBinary = 1;
    }

	if(pdfName == undefined){
		pdfName = 'report.pdf';
	}
    
	// append .pdf to filename if not found in filename
    if(pdfName.indexOf('.pdf') === -1) {
      	pdfName = pdfName + '.pdf';
    }

	// We need to do some cleaning up of the incoming HTML...
	
 if(pdfName == 'CoAEMSP Summary Tracking.pdf'){
		if($("#New").length == 0){
		var x = $(".table-container").html();
		 
		splitTables($("#fisdap-report-Fisdap_Reports_Accreditation3c2-0"), 15, [1]);
		$("body").append("<div id='New' style='zoom:200%; float:left;-webkit-backface-visibility: hidden;  -webkit-transform: translateZ(0) scale(1.0, 1.0);transform: translateZ(0);'></div>");
		$("#New").html("<h2>CoAEMSP Summary Tracking</h2>" + $("#report-info-header").html() + " " +$(".table-container").html());
		$('#New #export-report-links').hide(); 
		$('#New h3').hide(); 
      
		var eurekapdf = new jsPDF('p', 'pt', 'a4');
		var options = {
            background: '#fff',
			pagesplit: true			
        };
		eurekapdf.addHTML($("#New"), 15, 20, options, function() {
			eurekapdf.setFontSize(16);
			eurekapdf.setTextColor(0, 255, 0);
			eurekapdf.save(pdfName);			
		});	
		$( ".table-container" ).html(x);
		$("#New").hide();
		}
		return false;	
	}
	else if(pdfName == 'Eureka Report.pdf'){		
		if($("#New").length == 0){		
		$(".eureka_attempts_table_wrapper").css({'max-height' : '1500px'});	
		$(".eureka_attempts_table").css({'margin-top' : '0'});	
			$("body").append("<div id='New' style='zoom:-100%;float:left;-webkit-backface-visibility: hidden;-webkit-transform: translateZ(0) scale(1.0, 1.0);transform: translateZ(0);'></div>");
			$("#New").html($(".eureka_report_wrapper").html());
					
			var eurekapdf = new jsPDF('p', 'pt', 'a4');
			var options = {
				background: '#fff',	
			};
			eurekapdf.addHTML($("#New"), 15, 15, options, function() {
				eurekapdf.setFontSize(12);
				eurekapdf.setTextColor(0, 255, 0);
				eurekapdf.save(pdfName);			
			});	

			
			
			
			$("#New").hide();
			$(".eureka_attempts_table_wrapper").css({'max-height' : '550px'});
		}
		return true;	
	}
	
	else{
	// Are we in http or https? let's be consistent with where the browser is
        if (typeof(window.location.protocol) != 'undefined') {
		var protocol = window.location.protocol;
	} else if (typeof(document.location.protocol) != 'undefined') {
		var protocol = document.location.protocol;
	} else {
		var protocol = 'http:';
	}

	// Start by collecting all of the link tags, and converting them to absolute
	// paths as opposed to relative ones.
	htmlHead = $("<head></head>");
	$('link').each(function(index, element){
		newLink = $(element).clone(true, true);
		
		newLink.attr('href', protocol + "//" + document.domain + $(element).attr('href'));

		htmlHead.append(newLink);
	});
	
	// Let's check for any tables that have been affected by jquery.tableScroll() plugin.
	divContents.find('.tablescroll').each(function() {
		var origTable = $('table.tablescroll_head', this);
		$('.tablescroll_wrapper table tbody', this).appendTo(origTable);
		$('.tablescroll_wrapper', this).remove();
		
		// fix a couple of CSS styles that have become inappropriate because of the undo above
		$(origTable).removeClass('tablescroll_head');
		$('thead', origTable).addClass('tablescroll_head');
	});	
	
	// Next, change any anchor tags to spans.  No point having links in a PDF...
	divContents.find('a').each(function(index, element){
		jqEl = $(element);
		jqEl.replaceWith($("<span class='" + jqEl.attr('class') + "'>" + jqEl.html() + "</span>"))
	});

	// Finally change all image paths to absolute if they are relative (if they
	// start with a "/" or a "\"
	divContents.find('img').each(function(index, element){
		imageSrc = $(element).attr('src');
		
		if(imageSrc[0] == "\\" || imageSrc[0] == "/"){
			$(element).attr('src', protocol + "//" + document.domain + imageSrc);
		}
		
	});
	
	divContents.parents().find("body").css("background", "#fff");
	
	// remove anything with a class of no-pdf...
	divContents.find('.no-pdf').remove();
	postObject = {};
	
	postObject.htmlHead = "<head>" + htmlHead.html() + "</head>";
	postObject.pdfContents = divContents.html();
	postObject.pdfName = pdfName;
	postObject.useLegacyBinary = useLegacyBinary;

	// let's see if there are some pdf options, too
	var options = '';
	if ($("#pdf_orientation_type").length > 0) {
		options += "<input type='hidden' name='orientation' value='" + $("#pdf_orientation_type").val() + "' />";
	}
	
	// let's try creating a little form to submit
	var newForm = $("<form id='pdfGenerate' method='post' action='/pdf/create-pdf' class='hidden'>"+
					"<input type='hidden' name='contentEncoded' value='1' />"+
					"<input type='hidden' name='pdfName' value='" + escape(postObject.pdfName) + "' />"+
					"<input type='hidden' name='useLegacyBinary' value='" + escape(postObject.useLegacyBinary) + "' />"+
					"<input type='hidden' name='htmlHead' value='" + escape(postObject.htmlHead) + "' />"+
					"<input type='hidden' name='pdfContents' value='" + escape(postObject.pdfContents) + "' />"+
					options+
					"</form>");
	
	$('#'+divId+' .pdfLink').find('form').remove();
	$('#'+divId+' .pdfLink').append(newForm);
	
	// if we're emailing this pdf, add email details and post the form to /pdf/email-pdf
	if ($("#pdf_export_type").length > 0 && $("#export_type").find(".ui-state-active").attr("for") == 'email') {
		// add some more stuff to the form
		var emailInfo = "<input type='hidden' name='subject' value='" + escape($("#email_subject").val()) + "' />" +
						"<input type='hidden' name='recipients' value='" + escape($("#email_recipients").val()) + "' />" +
						"<input type='hidden' name='template' value='" + escape($("#email_template").val()) + "' />" +
						"<input type='hidden' name='note' value='" + escape($("#email_note").val()) + "' />";
		$("#pdfGenerate").append(emailInfo);
		$.post(
		    '/pdf/email-pdf',
		    $("#pdfGenerate").serialize()
		);
		return true;
	} else {
		// otherwise, just submit the form to /pdf/create-pdf, which creates the pdf and launches the download modal
		$('#pdfGenerate').submit();
		return true;
	}
	
	return false;
	}
	return false;
}
function splitTables($tables, chunkSize, fixCols) {
  $table = $($tables);
  fixCols = fixCols || [];
  fixCols = fixCols.sort();
  //chunkSize -= fixCols.length;
  var rowLength = $('tr:first>*', $table).length;
  var n = Math.ceil(rowLength / chunkSize);
  var bufferTables = [];
  var numberList = range(1, rowLength);

  for (var i = 1; i <= n; i++) {
    var colList = fixCols.slice(0);
    while (colList.length < chunkSize && numberList.length > 0) {
      var index = numberList.shift();
      if (colList.indexOf(index) == -1) {
        colList.push(index);
      }
    }

    var $newTable = $table.clone(true)
    for (var index = 1;
      (index <= rowLength); index++) {
      if (colList.indexOf(index) == -1) {
        $('tr>:nth-child(' + index + ')', $newTable).hide();
      }
    }
    bufferTables.push($newTable)
  }

  $(bufferTables.reverse()).each(function(i, $el) {
    $('<br/>').insertAfter($table);
    $el.insertAfter($table);
  });
  $table.remove();
}
function range(start, end) {
  var array = new Array();
  for (var i = start; i <= end; i++) {
    array.push(i);
  }
  return array;
}
