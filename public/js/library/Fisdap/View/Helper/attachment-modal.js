$(document).ready(function() {
    // initialize the attachment modal
    $("#attachmentModal").dialog({
        'modal': true,
        'autoOpen': false,
        'resizable': false
    });
});

// initialize the generic attachments modal
function initAttachmentModal() {
    // buttonize the buttons
    $("#attachmentModal .button").button();

    // remove errors
    $('.input-error').removeClass('input-error');

    // close the modal
    $("#attachment-modal-buttons .closeModal").click(function(e){
        e.preventDefault();
        $("#attachmentModal").dialog("close");
    });
}

/**
 * Initialize the view attachment modal
 */
function initViewAttachmentModal() {
    // initialize the modal
    $("#attachmentModal").dialog({
        title: "View Attachment",
        width: 770,
        'position': { my: "center top", at: "center top+25", of: window },
        open: function() {
            $(".download-column a").blur();
            $(".ui-dialog-titlebar").show();
        }
    });

    initAttachmentModal();
}

/**
 * Initialize the edit/add attachment modal
 */
function initEditAttachmentModal(attachmentId, afterSuccess) {
    if (attachmentId) {
        var title = "Edit attachment";
    } else {
        var title = "Add attachment";
    }

    // initialize the modal
    $("#attachmentModal").dialog({
        title: title,
        width: 525,
        open: function() {
            $(".download-column a").blur();
            $(".ui-dialog-titlebar").show();
        }
    });

    // do some styling stuff
    $("#attachmentModal .chzn-select").css("width", "200px").chosen();
    if ($("#fileName").val()) {
        $("#upload-name").html($("#fileName").val());
    } else {
        $("#upload-name").html("(no file chosen)");
    }

    // when the file changes, update the description and the name prompt
    $("#upload").change(function() {
        updateFilename(this);
    });

    $("#attachment-modal-content form").submit(function() {
        $(this).ajaxSubmit({
            target:   '#output',   // target element(s) to be updated with server response
            beforeSubmit:  beforeSubmit,  // pre-submit callback
            success:       afterSuccess,  // post-submit callback
            uploadProgress: OnProgress, //upload progress callback
            dataType: 'json'
        });
        return false;
    });

    // submit the form
    $("#attachment-modal-buttons .saveAttachmentForm").click(function(e){
        e.preventDefault();
        blockUi(true, $("#attachment-modal-buttons"), "throbber");
        resizeOverlay($("#attachment-modal-buttons"));
        $("#attachment-modal-content form").submit();
    });

    initAttachmentModal();
}

function updateFilename(file) {
    if (!file.value) {
        var fileName = "(no file chosen)";
    } else {
        var fileName = file.value.split("\\").pop();
    }
    $("#upload-name").html(fileName);
    $("#name").val(fileName.substring(0, 128));
}

/**
 * HELPER FUNCTIONS
 */

// a helper function to add form errors to this form
function addErrors(errors) {
    // remove existing errors
    $('.form-errors').remove();
    $('label').removeClass('prompt-error');
    $('.input-error').removeClass('input-error');

    htmlErrors = '<div class=\'form-errors alert\'><ul>';

    $.each(errors, function (elementId, msgs) {
        $('label[for=' + elementId + ']').addClass('prompt-error');
        $('#' + elementId).addClass('input-error');
        $.each(msgs, function (key, msg) {
            htmlErrors += '<li>' + msg + '</li>';
        });
    });

    htmlErrors += '</ul></div>';

    $('#attachment-modal-content form').prepend(htmlErrors);

    blockUi(false, $("#attachment-modal-buttons"));
}

// do some client side checking before we even try to submit this form
function beforeSubmit(){

    // if this is iOS 8.0.*, check if this is Safari; there's a bug that makes file upload not work
    if (iOSversion() == 8.0) {
        if (isSafari()) {
            var errors = {"attachmentModal": ["This version of iOS does not support file attachments on Safari. Please update your iOS or add your attachment using the Fisdap app."]};
            addErrors(errors);
            return false;
        }
    }

    //check whether client browser fully supports all File API
    if (window.File && window.FileReader && window.FileList && window.Blob)
    {
        // if we prompted the user to upload a file, check it out
        if ($('#upload').length > 0) {

            // make sure we have a file
            if (!$("#upload").val()) {
                var errors = {"uploadButton": ["Please choose a file to upload."]};
                addErrors(errors);
                return false;
            }

            // make sure the file name is not too long
            if ($('#upload').val().length > 128) {
                var errors = {"uploadButton": ["That file name is too long. Please rename your file and try again."]};
                addErrors(errors);
                return false
            }

            var fsize = $('#upload')[0].files[0].size; //get file size
            var ftype = $('#upload')[0].files[0].type; // get file type
//console.log(ftype);

            // blacklist file types
            switch (ftype) {
                case 'application/x-msdownload':
                case 'application/x-rar-compressed':
                case 'application/x-bittorrent':
                case 'application/x-sh':
                case 'application/x-csh':
                case 'text/x-c':
                case 'text/css':
                case 'application/x-debian-package':
                case 'application/x-doom':
                case 'application/x-gtar':
                case 'application/java-archive':
                case 'application/java-vm':
                case 'application/x-java-jnlp-file':
                case 'application/java-serialized-object':
                case 'text/x-java-source:java':
                case 'application/javascript':
                case 'application/json':
                case 'application/mac-binhex40':
                case 'application/vnd.macports.portpkg':
                case 'application/vnd.ms-cab-compressed':
                case 'audio/midi':
                case 'application/vnd.nokia.n-gage.data':
                case 'application/vnd.nokia.n-gage.symbian.install':
                case 'application/vnd.palm':
                case 'text/x-pascal':
                case 'application/x-chat':
                case 'application/x-font-type1':
                case 'application/x-font-linux-psf':
                case 'application/x-font-snf':
                case 'application/sdp':
                case 'application/x-shar':
                case 'application/x-stuffit':
                case 'application/x-stuffitx':
                case 'application/vnd.trueapp':
                case 'application/x-font-ttf':
                case 'application/x-font-woff':
                case 'application/x-dosexec':
                    var errors = {"uploadButton": ["That file type is not supported. Please choose a different file to attach."]};
                    addErrors(errors);
                    return false;
                case 'application/zip':
                case 'application/x-zip-compressed':
                    var errors = {"uploadButton": [".zip is not a supported file type. Please upload each file individually."]};
                    addErrors(errors);
                    return false;
                case '':
                    // IE can't handle some file types (like pdfs), so we'll let the back end deal with blocking stuff
                    if (!isIE()) {
                        var errors = {"uploadButton": ["Unknown file type. Please choose a different file to attach."]};
                        addErrors(errors);
                        return false;
                    }
            }

            // Allowed file size is less than 10 MB (1048576 = 1 MB)
            if (fsize >= 10485760) {
                var errors = {"uploadButton": ["Please choose a file that is 10MB or smaller."]};
                addErrors(errors);
                return false;
            }
        }
    } else {
        var errors = {"attachmentModal": ["The upload feature is not supported by your browser. Please upgrade your browser."]};
        addErrors(errors);
        return false;
    }
}

function OnProgress(event, position, total, percentComplete) {
    // show progress bar
    $('#attachmentModal .progressbox').css("opacity", "1");
    $('#attachmentModal .progressbar').width(percentComplete + '%') //update progressbar percent complete
    $('#attachmentModal .statustxt').html(percentComplete + '%'); //update status text
    if (percentComplete > 0) {
        $('#attachmentModal .statustxt').css('color', '#000'); //change status text to white after 50%
    }
}

function iOSversion() {

    // if this is an iOS, return the version number
    if (/iP(hone|od|ad)/.test(navigator.platform)) {
        var v = (navigator.appVersion).match(/OS (\d+)_(\d+)_?(\d+)?/);
        return parseInt(v[1], 10) + parseInt(v[2], 10)/10;
    }

    return 0;
}

function isSafari() {
    var isSafari = /Safari/.test(navigator.userAgent) && !/CriOS/.test(navigator.userAgent) && /Apple Computer/.test(navigator.vendor);
    return isSafari;
}

function isIE() {
    var isIE = /MSIE/.test(navigator.userAgent) || /Trident\/7\./.test(navigator.userAgent);
    return isIE;
}


