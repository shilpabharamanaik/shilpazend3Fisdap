$(function(){
    $("#uploadTrigger").button();
    $("#cancelButton").button();
    $("#chooseFile").button();
    $("#downloadFile").button();
    $("#cancelButton").children().css("font-weight", "normal");
    
    if($.browser.msie && $.browser.version == "8.0"){
        $("#uploadTrigger").hide();
    }
    
    // the dialog box for uploading a csv file
    $("#uploaderDialog").dialog({
        modal:true,
        autoOpen:false,
        resizable:false,
        width:650,
        minHeight:380,
        title:"Upload Spreadsheet"
        });
    
    $("#override-label").click(function(){
        if($("input#override").attr("checked")){
            $('input#override').removeAttr('checked');
        }
        else {
            $('input#override').attr('checked','checked');
        }
    });

        
    // open/close triggers for dialog          
    $("#uploadTrigger").click(function(e){ e.preventDefault(); $("#uploaderDialog").dialog('open'); });
    $("#cancelButton").click(function(){$("#uploaderDialog").dialog('close'); });
    
    // get all the params we have in our view
    // we'll need these to display warning/error/success messages
    var rowsAvailable = parseInt($("#rowsAvailable").text());
    var additionalStudents = parseInt($("#additionalStudents").text());
    var override = parseInt($("#override").text());
    
    // will add the missingValue class to all inputs/selects
    // will remove the missingValue class when the inputs/selects are changed
    function changeMissingValueClass(type, numberPerRow){
        var spots = 0;
        $("tr").each(function(){spots++;});
        spots = spots - 1;
        var filledSpots = spots - rowsAvailable;
        var count = 0;
    }
    
    function getS(quantity){
        if(quantity > 1){
            return "s";
        }
        else {
            return "";
        }
    }
    
    function getWasWere(quantity){
        if(quantity > 1){
            return "were";
        }
        else {
            return "was";
        }
    }
    
    
    // if the uploadResult div is present (we've uploaded a csv file)
    if($("#uploadResult").length > 0){
        // style the inputs (add the missing value class)
        //changeMissingValueClass($("select"), 2);
        //changeMissingValueClass($("input"), 5);
        
        $("select").each(function(){
            var row = $(this).parent().parent();

            // if the select box doesn't have a default value
            if($(this).val() == 0){
                var emptyRow = true;
                var tableCell = row.children();
                
                tableCell.each(function(){
                    //console.log($(this).find("input").val());
                   if($(this).find("input").val()){
                        emptyRow = false;
                   }
                });
                
                if(!emptyRow){
                    $(this).addClass("missingValue");
                }
            }
            
        });
        
        // display the success/warning boxes
        // show these if
        if(thereIsAWarning()){
            // display the warning box
            $("#warningUploadResult").show();
            
            // display a warning message if some data was not readable
            if(missingValuesFound()){
                $("#notReadableWarning").show();
            }
            
            // display a warning message if distributed codes were found
            // we'll tell the user how these codes were handled based on the "overridden" checkbox
            if(distributedCodesFound()){
                if(override == 1){
                    $("#hasDistributedUsedWarning").show();
                }
                else {
                    $("#hasDistributedNotUsedWarning").show();
                }
            }
            
            // display the message that some data was not readable
            // (we have at least one input with the missing value class)
            if($(".missingValue").length != 0){
                $("#notReadable").show();
            }
            else {
                $("#notReadable").hide();
            }
        }
        // there is no need to display the success/warning boxes
        else {
            $("#warningUploadResult").hide();
        }
    }
    
    function thereIsAWarning(){
        // there are missing values
        // additional students that were found in the file but not added because of space
        // if there were distributed activation codes already on the form (we'll need to tell them how we handled those)
        if(missingValuesFound()
           || distributedCodesFound()){
            return true;
        }
        else {
            return false;
        }
    }
    
    function missingValuesFound(){
        if($(".missingValue").length != 0){
            return true;
        }
        else {
            return false;
        }
    }
    
    function distributedCodesFound(){
        if($(".distributed").length != 0){
            return true;
        }
        else if ($(".useAnywaysText").length != 0){
            return true;

        }
        else {
            return false;
        }
    }
    
    
    // some validation for the uploading form
    $("#uploadSave").click(function(event){
        var extension = getExtension($("input#file").val());
        
        // if they haven't selected any files
        if(!extension){
            $("#errors").slideDown();
            $("input#file").val("");
            $("#fileName").text("no file chosen");
            event.preventDefault();
        }
        // there is a csv file selected, continue with the upload process
        else {
            //var throbber =  "<img id='throbber' src='/images/throbber_small.gif'>";
            //$(this).fadeOut("fast");   
           // $("#uploadContainer").append(throbber);
        }
    });

    // some styling fixes for the dialog box
    $("#uploaderDialog").dialog({
        open: function(event, ui) {
            $("a").blur();
            $("input").blur();
            $("#fileName").text("no file chosen");
            $("input#file").val("");
            if(distributedCodesFound()){
                $("#distributedModalWarning").show();
                $("ol#steps").css("height", "380px");
            }
            else {
                $("#distributedModalWarning").hide();
                $("ol#steps").css("height", "310px");
            }
        }
       
    });
    
    // some styling fixes for the dialog box
    $("#uploaderDialog").dialog({
         close: function(event, ui){
            $("#errors").hide();
        }
    });
    
    // gets the extension of an uplaoded file
    function getExtension(fileName){
        var extension = fileName.split(".");
        return extension[1];
    }

    // when the user selects a file
    $("input#file").change(function(){
        // remove the path for display
        var fileName = $(this).val().split("C:\\fakepath\\");
        var extension = getExtension($(this).val());
        
        // if our file isn't a csv, display an error message and set the form back to iys default state
        if(extension != "csv"){
            $("#errors").slideDown();
            $(this).val("");
            $("#fileName").text("no file chosen");
        }
        else {
            $("#errors").slideUp();
            if( $.browser.mozilla ){
                $("#fileName").text(fileName[0]);
            }
            else {
                $("#fileName").text(fileName[1]);
            }
        }
    });
    
    // since we're usinga  fancy input, we need to manually add the hover affect for the "choose file" button
    $("input#file").hover(function(){
       $("#chooseFile").addClass("ui-state-hover");
        
    }, function(){
        $("#chooseFile").removeClass("ui-state-hover");
    });

});
