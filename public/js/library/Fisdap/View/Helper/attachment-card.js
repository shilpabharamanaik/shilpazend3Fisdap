$(document).ready(function() {
    $('.attachment-card-contents').click(function () {
        var card = $(this).closest('.attachment-card');
        blockUi(true, card, "no-msg");

        var attachmentId = card.attr('attachment-id');
        var shiftType = card.data('shift-type');
        var shiftId = card.data('shift-id');
        showAttachmentModalFromAttachmentCard(attachmentId, shiftType, shiftId, card);
    });
});

function showAttachmentModalFromAttachmentCard(attachmentId, shiftType, shiftId, card) {
    $.post("/skills-tracker/shifts/generate-view-attachment", {
        "attachmentId": attachmentId,
        "shiftType": shiftType,
        "shiftId": shiftId
    }, function (resp) {
        $("#attachment-modal-content").html(resp.content);
        $("#attachment-modal-buttons").html(resp.buttons);
        initViewAttachmentModal();
        $("#attachmentModal").dialog("open");
        blockUi(false, card);

    });
}