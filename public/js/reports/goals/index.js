$(function(){
    // Define all the elements we're interested in working with to prevent excess querying
    var hiddenStudentId = $('#student').val();
    var goalsWidget = $('.goals-widget');
    var aboutStudent = $('.about-student');
    var availableList = $('#available-list');

    // If we're a student with a valid hidden ID, show us our page
    if(isNormalInteger(hiddenStudentId)) {
        goalsWidget.show();
    }
    
    // If we're an instructor with a valid student picked, show us our page
    if(isNormalInteger(availableList.val())) {
        reloadWidget();
    }

    availableList.on('change', function() {
        reloadWidget();
    });
    
    function reloadWidget() {
         // Get the currently selected student
        var selectedStudentId = availableList.val();

        // Ensure our selected student id warrants a change in view
        if(!isNormalInteger(selectedStudentId)) {
            return;
        }
        
        blockUi(true);
        
        // Clear the widgets previous state
        goalsWidget.slideUp().empty();
        aboutStudent.slideUp().empty();

        // When.all takes an array of functions, each of which must return a deferred. 
        // Once all of the promises are resolved, the done function is executed.
        // Done is passed an array as an argument. This array holds the results of each
        // deferred function that you pass into when.all.
        // 
        // This effectively means that the results of the ajax call at the 0 position, will
        // be in the 0 position of the results array passed to done. Similarly, the results 
        // of loadWidgets, which is at position 1, will be at position 1 in the results array.
        $.when.all([
            
            // Redraw the about-student view-helper using the newly selected student 
            $.ajax({
                url: '/reports/goals/about-student',
                method: 'POST',
                // dataType: 'json',
                data: {
                    "options": {
                        "student":selectedStudentId,
                        "helpers":{
                            "profile-pic": {
                               "options": {
                                   "size":"150"
                               }
                            },
                            "info":true,
                            "badges":true
                        }
                    }
                }
            }),

            // Force a redraw of the goals widget using the newly selected student
            loadWidgetsDeferred(
                'goals-widget',
                'test',
                '{"explicitStudentId":'+selectedStudentId+',"allowMinimize":false,"minimizeMode":"expanded"}'
            )

        ]).done(function(results) {

            // Swap out the html of the elements with the corresponding results
            aboutStudent.html(results[0][0]);
            goalsWidget.html(results[1][0]);

            // Redraw the page, showing the updated goals and about student elements
            goalsWidget.add(aboutStudent).show();
            blockUi(false);
            
        });
    }
});

// Check to make sure string is normal and over zero
function isNormalInteger(str) {
    var n = ~~Number(str);
    return String(n) === str && n > 0;
}