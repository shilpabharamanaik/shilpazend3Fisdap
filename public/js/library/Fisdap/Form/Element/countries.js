//Javascript for Fisdap_Form_Element_Countries and Fisdap_Form_Element_States

$(document).ready(function(){

	initStates();

	$("select.country").on('change', function(event) {
		populateStates($(this), null);
	});

});

function initStates()
{
	$("select.country").each(function(i, el){
		populateStates($(el));
	});
}

function populateStates(country)
{
	var state = $("select#" + country.attr('data-state'));
	var stateId = $(state).val();

	//base.attr('disabled', 'disabled');

	var url = '/ajax/get-states';
	var data = { "countryId" : country.val() };
	$.ajax({
	  type: 'POST',
	  url: url,
	  data: data,
	  async: false,
	  success: function(resp) {
		var html = '';

		if (resp.length == 0) {
			state.attr("disabled", "disabled").parents(".form-prompt").hide();
		} else {
			state.removeAttr("disabled").parents(".form-prompt").show();
			$.each(resp, function(id, name) {
				html += "<option value='" + id + "'";
				if (stateId == id) {
					html += " selected='selected' ";
				}
				html += ">" + name + "</option>";
			});
			state.html(html);
		}
	},
	  dataType: 'json'
	});
}