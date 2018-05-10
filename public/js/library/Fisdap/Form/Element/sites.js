//Javascript for Fisdap_Form_Element_Sites and Fisdap_Form_Element_Bases

$(document).ready(function(){
	
	$(document).on('change', "select.sites", function(event) {
		populateBases($(this).val(), null);
	});
	
});

function initSites(event)
{
	var site = $("select.sites");
	var base = $("select.bases");
	var siteId = site.val();
	var baseId = base.val();
	
	//initial call to store bases
	populateBases(siteId, baseId,event);
}

function populateBases(siteId, baseId,event)
{
	var shiftId = '';
	var base = $("select.bases"); 

	if(event){
	var shiftId = event.currentTarget.getAttribute('shiftid');
	} 

	// If using mobile, this should be true as it is set on the html class in the layout
	// Using this method as it is faster, per: http://jsperf.com/jquery-selector-on-class-vs-data/7
	var is_mobile = $(".fisdap-is-mobile").length > 0;

	var throbber =  $("<img id='update-base-throbber' src='/images/throbber_small.gif' style='float:right; margin:5px;'>");

	if(is_mobile !== true) {
		$("#base_chzn").prepend(throbber);
		$("#base_chzn a").css("opacity", ".3");
	}

	var url = '/ajax/get-bases';
	var data = { "siteId" : siteId, "shiftId": shiftId };
	$.ajax({
	  type: 'POST',
	  url: url,
	  data: data,
	  async: false,
	  success: function(resp) {
		$(base).empty();
		
		var option = '';
		$.each(resp, function(id, name) {
			option = "<option value='" + id + "'";
			if (baseId == id) {
				option += " selected='selected' ";
			}
			option += ">" + name + "</option>";
			$(base).append(option);
		});

		if(is_mobile !== true) {
			$(base).trigger("liszt:updated");
			$("#base_chzn a").css("opacity", "1");
		}
		
		$("#update-base-throbber").remove();
	  },
	  dataType: 'json'
	});
}
