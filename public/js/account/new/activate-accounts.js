$(function(){

/*
    $("#useAnywaysFields").each(function(){
		console.log($(this).parent().parent().text());
		if(!$(this).parent().parent().hasClass("has-errors")){
			$(this).hide();
		}
    });
	*/
	
   
   $(".useAnywaysTrigger").each(function(){
		if($(this).parent().parent().hasClass("has-errors")){
			$snArray = $(this).parent().attr("id").split("useAnywaysText");
            $snId = $snArray[1];
            $(this).parent().hide();
			
			$(".hiddenCell").each(function(){
               if($(this).hasClass($snId)){
                    $(this).show();
               }
            });
		}
		
        $(this).click(function(e){
            e.preventDefault();
            $snArray = $(this).parent().attr("id").split("useAnywaysText");
            $snId = $snArray[1];
            $(this).parent().fadeOut("500");
            
            $(".hiddenCell").each(function(){
               if($(this).hasClass($snId)){
                    $(this).delay(500).fadeIn("500");
                    if($(this).find('select')){
                    }
               }
            });
            
        });
   });
   
   
   $(".distributed").each(function(){
        setToolTipFunctions($(this), $(this).find(".distributedToolTip"));
   });
   
   	function setToolTipFunctions(trigger, toolTip){
		trigger.hover(function(){
			toolTip.fadeIn("fast");
		}, function() {
			toolTip.fadeOut("fast");
		});
	}
	
    
});