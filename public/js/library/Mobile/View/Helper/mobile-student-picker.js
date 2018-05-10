$(function() {
    var throbber = $("#throbber").hide();
    $("#searchString").focus().keypress(function(e) {
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code == 13) { //Enter keycode
            findStudents();
        }
    });
    
    $("#find-student-btn").click(findStudents);
    
});

function findStudents() {
    //Do nothing if they didn't enter part of a student's name
    if ($("#searchString").val() == "") {
        return;
    }
    
    var throbber = $("#throbber");
    throbber.show();
    
    disableElements($("#mobile-student-picker").find("input"));
    $.post("/mobile/index/find-students/", {"searchString" : $("#searchString").val() },
       function(response) {
        //Base URL for mobile skills tracker
        var url = "/mobile/index/index/studentId/"
        
        //The html representing the student list
        var html = "";
        
        //A counter to keep track of how many students were found
        var count = 0;
        
        //Keep track of the most recent studentId read
        var studentId = 0;
        
        //Loop over results and add urls
        $.each(response, function(index, el) {
            html += "<a href='" + url + index + "'>" + el + "</a><br>";
            studentId = index;
            count++;
        });
        
        disableElements($("#mobile-student-picker").find("input"));
        
     //   console.log(count);
        
        //If there was only one result, just redirect
        if (count == 1) {
            window.location = url + studentId;
            return;
        } else if (count == 0) {
            html = "<p>No students found.</p>";
        }
        
        
        $("#student-search-results").html($(html).fadeIn());
        throbber.hide();
       }, "json"
    );
}

function disableElements(elements) {
    $.each(elements, function(index, el) {
        if ($(el).is(':disabled')) {
            $(el).removeAttr('disabled');
        } else {
            $(el).attr('disabled', 'disabled');
        }
    });
}