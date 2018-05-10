$(function(){
    $('a#backButton').button();
    $(".ui-button-text").css("line-height", "1");
   
    $("label").each(function(){
        if($(this).attr('for') != "save"){
            var labelText = $(this);
            var initialText = $(this).text();
            var textArray = initialText.split('<');
            var numberOfLines = textArray.length;
            
            // save what we know about this radio
            var radio = $(this).find("input");
            var radioValue = radio.val();
            var radioId = radio.attr('id');
            var radioName = radio.attr('name');
            $(this).text("");
            
            var finalString = "<div class='user'><div class='radioWrapper'>";
            finalString += "<input type='radio' name='" + radioName + "' id='" + radioId + "' value='" + radioValue + "'>";
            finalString += "</div><div class='radioText'>";
            
            // for each piece of the spilt array, append the
            // part to the original value
            $.each(textArray, function(index, value) {
                finalString += " " + value;
                if(value != ""){
                    if(index != numberOfLines-1){
                        finalString += "<br />";
                    }
                }
            });
            
            finalString += "</div><div style='clear:both;'></div></div>";
            labelText.append(finalString);
        }
        else {
            $(this).hide();
        }
    });

    var hoverColor = "#f8f8f8";
    var selectedColor = "#eee";
    var normalColor = "#fff";
    
    setDefaultSelection($("#users-0"), selectedColor);
    
    $(".user").click(function(){
        $("#save").val("Continue >");
        
       $(".user").each(function(){
            $(this).css("background-color", normalColor);
            $(this).removeClass("selected");
       });
        $(this).css("background-color", selectedColor);
        $(this).addClass("selected");
    });
    
    $(".user").hover(function(){
        if(!$(this).hasClass("selected")){
            $(this).css("background-color", hoverColor);
        }
    }, function(){
        if($(this).hasClass("selected")){
            $(this).css("background-color", selectedColor);
        }
        else {
            $(this).css("background-color", normalColor);
        }
    });
    
    function setDefaultSelection(selectedOption, selectedColor){
        selectedOption.attr('checked', "checked");
        selectedOption.parent().parent().addClass("selected");
        selectedOption.parent().parent().css("background-color", selectedColor);
        $("#save").val("Continue >");
    }
});
