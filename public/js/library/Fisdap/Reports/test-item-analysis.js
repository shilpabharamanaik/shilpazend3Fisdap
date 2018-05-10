/**
 * Created by jmortenson on 4/23/14.
 */
$(document).ready(function() {

    // register a global function as a class-specific callback
    // to operate on the DOM after report results are loaded
    window.reportResultsCallbacks.push(function() {
        $('.test-item-analysis-flag').button();
    });

    $("#report-content").on('click', 'a.test-item-analysis-flag', function(e) {
        e.preventDefault();
        var testItemId = $(this).attr('data-item-id');
        //var itemDistractorId = $(this).attr('data-distractor-id');

        $('.test-item-analysis-flag-form').dialog({
            'height' : 300,
            'width' : 450,
            'modal' : true,
            'open' : function () {
                $('.test-item-analysis-flag-save, .test-item-analysis-flag-cancel').button();
                $('.test-item-analysis-flag-save').click(function(e) {
                    e.preventDefault();
                    var url = '/ajax/send-test-item-analysis-flag-email';
                    var data = {
                        "test_id": $("input[name='test_id']").val(),
                        "item_id": testItemId,
                        "message": $("textarea[name='message']").val(),
                        "table": $("#fisdap-report-Fisdap_Reports_TestItemAnalysis-item-" + testItemId).wrap('<div/>').parent().html()
                    };
                    $.post(url, data, function (data) {
                            if (data.isError) {
                                console.log(data);
                                alert("Sorry, there was a problem sending your message.");
                            } else {
                                $(".test-item-analysis-flag-form").dialog("close");
                            }
                        }
                        , "json").fail(function (fail_data) {
                            alert("Sorry, there was a problem sending your message.");
                        }
                    );
                });
                $('.test-item-analysis-flag-cancel').click(function() {
                    $(".test-item-analysis-flag-form").dialog("close");
                });
            },
            'close' : function() {
                $(".test-item-analysis-flag-form input[name='item_id'], .test-item-analysis-flag-form input[name='distractor-id'], .test-item-analysis-flag-form textarea").val('');
                $(".test-item-analysis-flag-save, .test-item-analysis-flag-cancel").unbind('click');
            }
        });
    });

});
