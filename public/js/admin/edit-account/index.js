//Javascript for /admin/edit-account/index
$(document).ready(function(){
    //Add alternate coloring for odd rows
    $('.users-table .user_container:odd').addClass('alt');
    
    //Add alternate coloring for moused over row
    $(".user-container").mouseover(function(){$(this).addClass("over");}).mouseout(function(){$(this).removeClass("over");});
    
    //Add link to account view page (/admin/edit-account/view)
    $(".user-container").click(function() {
        location.href='/admin/edit-account/view/user_id/' + $(this).attr('id');
    })
});