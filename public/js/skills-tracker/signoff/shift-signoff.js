$(function(){
	$("#save").click(function(e){
		e.preventDefault();
		blockUi(true);

		$.post("/skills-tracker/signoff/validate-shift-signoff", $("#signoffForm").serialize(), function(response){
			if (typeof response == "object") {
				blockUi(false);
				
				//Add errors to page
				htmlErrors = '<div class=\'form-errors alert\'><ul>';
				$('label').removeClass('prompt-error');
				
				$.each(response, function(elementId, msgs) {
					$('label[for=' + elementId + ']').addClass('prompt-error');
					htmlErrors += '<li>' + msgs + '</li>';
				});
				
				htmlErrors += '</ul></div>';
				
				$('#signoffForm .form-errors').remove();
				$('#signoffForm').prepend(htmlErrors);
				$( 'html, body' ).animate( {scrollTop: $('#signoffForm .form-errors').offset().top}, 0);
			} else {
				window.location = "/skills-tracker/shifts";
			}
		}, "json");
	});
})