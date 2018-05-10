//Javascript for SkillsTracker_Form_Element_Subject

$(document).ready(function()
{
	var pickedType = $("input[name='subject[type]']:checked").val();
	toggleFields(pickedType);
    
	$(".subject-name").change(function() {
	        toggleFields(false);
	});
});

function toggleFields(pickedType)
{
    var subjectName = $(".subject-name");
    var subjectType = $(".subject-type");
    
    if (subjectName.val() != "Manikin") {
        $(subjectType[2]).parent().hide();
        $(subjectType[2]).parent().next().hide();
        $(subjectType[3]).parent().hide();
        $(subjectType[3]).parent().next().hide();
        
        //show
        $(subjectType[0]).parent().show();
        $(subjectType[0]).parent().next().show();
        $(subjectType[1]).parent().show();
        $(subjectType[1]).parent().next().show();
        
        var defaultType = 0;
    } else {
        $(subjectType[0]).parent().hide();
        $(subjectType[0]).parent().next().hide();
        $(subjectType[1]).parent().hide();
        $(subjectType[1]).parent().next().hide();
        
        //show
        $(subjectType[2]).parent().show();
        $(subjectType[2]).parent().next().show();
        $(subjectType[3]).parent().show();
        $(subjectType[3]).parent().next().show();
        
        var defaultType = 2;
    }
    
    //Make sure an option is checked
    if (pickedType) {
	$(subjectType[pickedType]).attr('checked', 'checked');
    } else {
	$(subjectType[defaultType]).attr('checked', 'checked');
    }
}