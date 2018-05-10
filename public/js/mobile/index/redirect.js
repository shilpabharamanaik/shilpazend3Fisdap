$(function(){
    $("#mobile-redirect").click(function(e) {
        updateCookieStatus();
        return true;
    });

    $("#full-site-redirect").click(function(e) {
        updateCookieStatus();
        //console.log("click");
        return true;
    });
});

function updateCookieStatus()
{
    if ($("input[name='dont_show']").is(":checked")) {
        $.cookie('dont_show_mobile_redirect', true, { expires: 365, path: '/' });
    }
}