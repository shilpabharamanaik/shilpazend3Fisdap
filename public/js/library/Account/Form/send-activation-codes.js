var emailParserRegexp = '[;,]';

$(function(){
    $("a.parse-email-link").click(function(e){
        e.preventDefault();
        clickedSubForm = $(this).parents("div.activation-codes");
        openEmailParserDialog();
    });
    
    $("a.preview-link").click(function(e){
        e.preventDefault();
        subForm = $(this).parents("div.activation-codes");
        
        data = {
            "orderConfigurationId" : subForm.find(".orderConfigurationId").val(),
            "message" : subForm.find(".message").val()
        }
        
        $.post("/account/new/preview-invitation", data, function(response){
            $(response).dialog({
                modal: true,
                resizable: false,
                draggable: false,
                width: 800,
                height: 600,
                title: "Invitation Email Preview",
                buttons: {"Ok" : function() {$(this).dialog("close")}}
            });
        }, "json");
    });
});

function openEmailParserDialog()
{
    emailDialog = $("<div class><form>Please enter a comma separated list of email address:<br><textarea id='emails' name='emails'></textarea></form></div>");
    emailDialog.dialog({
        modal: true,
        draggable: false,
        resizable: false,
        buttons: {"Ok" : function() {
            emails = emailDialog.find("textarea#emails").val().split(new RegExp(emailParserRegexp, "g" ));
            clickedSubForm.find(".serialNumber").each(function(index, el) {
                if (emails.length == 0) {
                    return;
                }
                $(el).val(emails.pop().trim());
            });
            $(this).dialog("close");
            $(this).remove();
            }},
    });
}