$(function () {
    // line up the drop down menu correctly

    if ($("#header_upper_nav").length) {
        var rightEdge = $("#header_upper_nav").width() - ($("#context-switcher").position().left + $("#context-switcher").width());
        $(".context-switcher-menu").css("right", rightEdge);
    }

    // show drop down on hover
    $("#context-switcher").hover(
        function () {
            $(".context-switcher-menu").show();
        },
        function () {
            setTimeout(function () {
                $(".context-switcher-menu").hide();
            }, 300);
        }
    );

    // switch context when they click the link
    $("a.switch-context").click(function(e){
        e.preventDefault();
        blockUi(true);
        var newContext = $(this).attr("data-contextId");
        $.post("/ajax/update-current-context",
            {"newContext" : newContext},
            function (response) {
                if (response.success) {
                    window.location = "/my-fisdap/";
                } else {
                    blockUi(false);
                }

            }, "json");
    });
});