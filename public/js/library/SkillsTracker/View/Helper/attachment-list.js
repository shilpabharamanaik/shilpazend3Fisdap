/**
 * Created by khanson on 4/6/15.
 */
$(document).ready(function() {
    doAttachmentListJqueryEvents();
    renumberRows();

    // if this is a read-only table, the user doesn't get the action buttons
    $("#attachment-table.read-only td.actions-column").remove();
});

function doAttachmentListJqueryEvents() {
    //remove previous jquery stuff because we're about to reapply them
    $('tr.attachment-row td').unbind('click');
    $('#add-attachment, .edit-attachment').unbind('click');
    $('.delete-attachment').unbind('click');


    // add/edit attachment button
    $('#add-attachment, .edit-attachment').click(function(event) {
        event.preventDefault();

        // set ui blocker
        var container = $(this).closest(".attachment-table-container");
        blockUi(true, container, "no-msg");

        var attachmentId = $(this).attr('data-attachmentid');
        var shiftId = $("#attachment-table").attr('data-shiftid');
        var tableType = $("#attachment-table").attr('data-tabletype');
        var afterSuccess;
        switch(tableType) {
            case 'signoff':
                afterSuccess = function(response) {
                    if (response.mode == 'error') {
                        addErrors(response.errors);
                    } else if (response.mode == 'limit') {
                        limitModal(response.errors);
                    } else {
                        $("#attachmentModal").dialog("close");
                        var row = $(response.html);

                        // Remove the null state
                        $("#attachment-table-empty").addClass('hidden');
                        $("#attachment-table").removeClass('hidden');

                        // Show the attachments table if hidden and add rows
                        $("#attachment-table table tbody").prepend(row);
                        row.find('td').click(selectRow).trigger('click');
                        row.parents('.fisdap-table-scrolling-container').scrollTo(row, 400);
                    }
                };
                break;
            default:
                afterSuccess = function(response) {
                    if (response.mode == 'error') {
                        addErrors(response.errors);
                    } else if (response.mode == 'limit') {
                        limitModal(response.errors);
                    } else {
                        $("#attachmentModal").dialog("close");
                        updateAttachmentList(response);
                    }
                };
                break;
        }
        $.post("/skills-tracker/shifts/generate-shift-attachment-form", {
                "attachmentId": attachmentId,
                "shiftId": shiftId,
                "tableType": tableType
            },
            function (resp) {
                $("#attachment-modal-content").html(resp.content);
                $("#attachment-modal-buttons").html(resp.buttons);
                initEditAttachmentModal(attachmentId, afterSuccess);
                $("#attachmentModal").dialog("open");
                blockUi(false, container);
                $(this).blur();
            }
        );
    });

    // delete attachment links
    $('.delete-attachment').click(function(event) {
        event.preventDefault();
        var attachmentId = $(this).attr('data-attachmentid');
        var row = $("tr.attachment-row[data-attachmentid='" + attachmentId + "']");
        var cell = $(this).closest(".action-cell");

        // create the function that will happen once the countdown is complete
        var deleteAction = function() {
            blockUi(true, $(row).find("div.action-cell"), "throbber");
            positionBlocker($(row));
            $.post("/attachments/delete-attachments", {
                    "attachmentIds": new Array(attachmentId),
                    "gateway": "Fisdap\\Api\\Client\\Shifts\\Attachments\\Gateway\\ShiftAttachmentsGateway",
                    "associatedEntityId": $("#attachment-table").attr('data-shiftid')
                },
                function (resp) {
                    $(row).slideUp().remove();
                    renumberRows();
                }
            );
        }

        // set up countdown
        delayedAction(cell, deleteAction, "deleteAttachment");

    });

    // open the view attachment modal
    $('tr.attachment-row td:not(.thumbnail-column):not(.actions-column)').click(function (e) {
        blockUi(true, $("#attachment-table"), "no-msg");
        var attachmentId = $(this).parent().attr('data-attachmentid');
        var shiftType = $("#attachment-table").attr('data-shifttype');
        var shiftId = $("#attachment-table").attr('data-shiftid');
        $.post("/skills-tracker/shifts/generate-view-attachment", {
                "attachmentId": attachmentId,
                "shiftType": shiftType,
                "shiftId": shiftId
            },
            function (resp) {
                $("#attachment-modal-content").html(resp.content);
                $("#attachment-modal-buttons").html(resp.buttons);
                initViewAttachmentModal();
                $("#attachmentModal").dialog("open");
                blockUi(false, $("#attachment-table"));
            });
    });

}

function updateAttachmentList(response) {
    if (response.mode == 'add') {
        var newRow = response.html;
        $("#attachment-table table tbody").append(newRow);
    } else {
        var newRow = response.html;
        var oldRow = $("tr.attachment-row[data-attachmentid='" + response.attachmentId + "']");
        $(oldRow).replaceWith(newRow);
    }

    // reapply all the jquery events for the table
    doAttachmentListJqueryEvents();

    // renumber the rows, too
    renumberRows();

    // then highlight the change
    var row = $("tr.attachment-row[data-attachmentid='" + response.attachmentId + "']");
    highlightElement(row, response.mode);
}

function renumberRows() {
    var count = 0;
    $("#attachment-table td.number-column").each(function() {
        count++;
        $(this).html(count);
    });

    // go ahead and show or hide the null state message based on the new count
    if (count > 0) {
        $("#no-attachments").hide();
    } else {
        $("#no-attachments").show();
    }
}

function highlightElement(element, mode) {

    if (mode == 'add') {
        $('html,body').animate({scrollTop: $(element).offset().top-100}, 'slow');
        setTimeout(function(){
            $(element).effect("highlight", 700);
        }, 200);
    } else {
        $(element).effect("highlight", 700);
    }
}

function limitModal(errors) {
    addErrors(errors);
    // if we reached the limit, get rid of the rest of the form and the add button
    $("#attachment-modal-content form div:not(.form-errors)").remove();
    $("#attachment-modal-buttons div.green-buttons").remove();
    $("#attachment-modal-buttons .closeModal span.ui-button-text").html("Ok");
    $("#add-attachment").remove();
}

