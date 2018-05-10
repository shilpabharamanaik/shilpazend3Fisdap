$(function () {

    $("a#exam-check-all").click(function(e){
        e.preventDefault();
        if($(this).text() == 'All'){
            $(this).text("");
            $(".exam_checkbox :input").attr('checked', true);
            $(this).text("None");
        }
        else {
            $(this).text("");
            $(".exam_checkbox :input").attr('checked', false);
            $(this).text("All");
        }    });

    $("a#interview-check-all").click(function(e){
        e.preventDefault();
        if($(this).text() == 'All'){
            $(this).text("");
            $(".interview_checkbox :input").attr('checked', true);
            $(this).text("None");
        }
        else {
            $(this).text("");
            $(".interview_checkbox :input").attr('checked', false);
            $(this).text("All");
        }    });
});