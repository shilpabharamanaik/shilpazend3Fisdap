$(function () {
    // if there are no students with transition course, show a notice to that effect
    if ($("#available-list option").length < 1) {
        $("#report-form-form").hide();
        $("#report-form").append("<div class='info'>No one in your program has purchased the Transition Course.</div>");
    }
});	
