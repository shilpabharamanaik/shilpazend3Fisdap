$(document).ready(function() {
    $("#save").button("option", "disabled", true);
    
    $("#agreeFlag").change(function(){
        $("#save").button("option", "disabled", !$(this).is(":checked"));
    })
    
    $("#privacyPolicyDialog").dialog({
        modal:true,
        autoOpen:false,
        resizable:false,
        width:700,
        height:450,
        title:"Fisdap Privacy Policy",
        buttons:[
            {
                text:"Ok",
                className:"gray-button",
                click: function() { $(this).dialog("close"); }
            }
        ]
        });
    
    $("#viewpp").click(function(){
       $("#privacyPolicyDialog").dialog('open'); 
    });
    
    $("a").each(function(){
        if($(this).text() == "Privacy Policy"){
            $(this).hide();
        }
        
        if($(this).text() == "Terms of Use"){
            $(this).hide();
        }

    });
    
});