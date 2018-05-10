function initCustomNarrativeModal() {
    $(".buttons :input").each(function(){
	if($(this).attr("checked")) {
	    $(this).parent().parent().addClass("selected_btn");
	} else {
	    $(this).parent().parent().addClass("unselected_btn");
	}
        if($(this).attr("value") == 2) {
            $(this).parent().parent().addClass("rounded_left");
        }
        if($(this).attr("value") == 32) {
            $(this).parent().parent().addClass("rounded_right");
        }
    });
    
    // this is what makes the table rows draggable and sortable (not for lte IE8)
    if( !($.browser.msie && $.browser.version <= 8.0)) {
        $("#narrative_sections tbody").sortable({
                handle: ".handle",
                cursor: "move",
		update: function(event, ui) {
			var tbody = $(this);
                        var tmp_id = 0;
                        var tmp_order_element = 0;
			tbody.sortable("option", "disabled", true);
                        tbody.children().each(function(i, row) {
                                tmp_id = $(row).attr('id').substring(4);
                                tmp_order_element = document.getElementById(tmp_id+"_order");
                                if (tmp_order_element) {
                                    tmp_order_element.value = i;
                                }
			});
                        tbody.sortable("option", "disabled", false);
		}
	});
    }
    
    // add the cluetip for this modal
    $('#customNarrativeHelp').cluetip({activation: 'click',
                        local:true, 
                        cursor: 'pointer',
                        width: 450,
						cluezIndex: 2000000,
                        cluetipClass: 'jtip',
                        sticky: true,
                        closePosition: 'title',
                        closeText: '<img width=\"25\" height=\"25\" src=\"/images/icons/delete.png\" alt=\"close\" />'});
    
    $(".buttons :input").change(function(){
        var row_id = $(this).parent().parent().parent().parent().attr("id").substring(4);
        updateRow(row_id);
    });
    
    $("input:text:visible:first").focus();

};

function updateRow(id) {
    updateButton($("#"+id+"_size-2"));
    updateButton($("#"+id+"_size-8"));
    updateButton($("#"+id+"_size-32"));
}

function updateButton(e) {
    if(e.attr("checked")) {
        e.parent().parent().addClass("selected_btn");
        e.parent().parent().removeClass("unselected_btn");
    } else {
        e.parent().parent().addClass("unselected_btn");
        e.parent().parent().removeClass("selected_btn");
    }
}

function deleteSection(id)
{
    var row = $("#row_" + id);
    var name = document.getElementById(id+"_name");
    var active = document.getElementById(id+"_active");
    
    blockUi(true);
    var completed = false;
	
    function complete() {
	if (completed) {
	    return;
	}
	completed = true;
	
        active.value = 0;
        var message = $("<td colspan='5' class='undo'>\""+name.value+"\" section deleted <a href='#' id='undo-delete-"+id+"'>Undo!</a></td>");
        row.append(message.fadeIn(1000));
        $('#undo-delete-' + id).click(function(event) {
            event.preventDefault();
            event.stopPropagation();
            undoDeleteSection(id);
        });
        blockUi(false);
    }
    
    row.children().fadeOut(1000, complete);
	
}

function undoDeleteSection(id)
{
    var row = $("#row_" + id);
    var active = document.getElementById(id+"_active");
    
    blockUi(true);
        
    active.value = 1;		
    row.children(":visible").remove();
    row.children().fadeIn(1000);
    blockUi(false);
	
}

function addNarrativeSection()
{
    var table = document.getElementById("narrative_sections");
    
    var counter = document.getElementById("section_count");
    var id = 'new_' + counter.value;
    
    // this is a really crappy way to add the section to the array
    var section_ids = document.getElementById("section_ids");
    var section_substring = section_ids.value.substring(section_ids.value.search("{"), section_ids.value.length - 1);
    var index = counter.value-1;
    section_ids.value = 'a:' + counter.value.toString() + ':' + section_substring + 'i:' + index.toString() + ';s:' + id.length + ':"' + id +'";}';
    
    var row = table.insertRow(-1);
    row.id = 'row_'+id;
    
    var grabber = row.insertCell(0);
    grabber.setAttribute("class", "handle");
    var textbox = row.insertCell(1);
    var sizeButtons = row.insertCell(2);
    sizeButtons.setAttribute("class", "buttons");
    var seedCheckbox = row.insertCell(3);
    var deleteButton = row.insertCell(4);
    grabber.innerHTML = '<img class="grabby-icon" title="Click and drag to reorder" src="/images/icons/wide_grabby.png">';
    textbox.innerHTML = '<input id="'+id+'_name" type="text" maxlength="50" value="Narrative" name="'+id+'_name">';
    sizeButtons.innerHTML = '<div class="unselected_btn rounded_left" onclick="updateRow(\''+id+'\')"><label><input id="'+id+'_size-2" type="radio" value="2" name="'+id+'_size"> Small</label></div>'+
                            '<div class="selected_btn" onclick="updateRow(\''+id+'\')"><label><input id="'+id+'_size-8" type="radio" value="8" checked="checked" name="'+id+'_size"> Med</label></div>' +
                            '<div class="unselected_btn rounded_right" onclick="updateRow(\''+id+'\')"><label><input id="'+id+'_size-32" type="radio" value="32" name="'+id+'_size"> Large</label></div>';
    seedCheckbox.innerHTML = '<div class="seed_checkbox"><input id="'+id+'_seed" type="checkbox" value="1" name="'+id+'_seed"></div>';
    deleteButton.innerHTML = '<a class="delete-section" title="delete section" onclick="deleteSection(\''+id+'\')" href="#"><img class="small-icon" src="/images/icons/delete.png"></a>';

    var formdiv = document.getElementById("form_div");
    var newdiv = document.createElement('div');
    newdiv.innerHTML = '<input id="'+id+'_active" type="hidden" value="1" name="'+id+'_active">' +
                       '<input id="'+id+'_order" type="hidden" value="'+ counter.value +'" name="'+id+'_order">';
    formdiv.appendChild(newdiv);
    counter.value++;
}

function openPreview()
{
	var form = document.getElementById("customNarrativeForm");
	form.setAttribute("action", "/skills-tracker/settings/narrative-preview");
	form.setAttribute("target", "_blank");
	form.submit();
}
