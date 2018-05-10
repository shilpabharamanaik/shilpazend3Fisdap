//Javascript for SkillsTracker_Form_Element_IvSite

$(document).ready(function()
{
	toggleIvSiteFields();
    
    $(".site-name").change(function() {
        toggleIvSiteFields();
    });
});

function toggleIvSiteFields()
{
    var siteName = $(".site-name");
    var siteSide = $(".site-side");
    
    if (siteName.val() == "other") {
		siteSide.parent().hide();
    } else {
		siteSide.parent().show();
    }
}