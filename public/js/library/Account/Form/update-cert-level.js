$(function(){
	$("#editCertCloseButton").button();
	$("#editCert").button();
	
    // the dialog box for email activation codes
    $("#certDialog").dialog({
        modal:true,
        autoOpen:false,
        resizable:false,
        width:300,
		height:180,
        title:"Edit Certification"
        });
	
		
	// open/close triggers for dialog          
    $("a#cert").click(function(){
		
	
		
		var codes = [];
	
		$("tr").each(function(){
			 var checkbox = $(this).find("input:checkbox");
			 if(checkbox.is(":checked")){
				codes.push(checkbox.val());
			}
		});
		
		$("#certDialog").dialog('open');

	});
	
    $("#editCertCloseButton").click(function(e){
		e.preventDefault();
		$("#certDialog").dialog('close');
	});
});