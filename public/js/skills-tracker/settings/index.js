$(function(){
    $("#close-button").button();
    $("#save-button").button();
    $("#cancel-button").button().css("color", "#666");

    
    if($.browser.msie || $.browser.mozilla){$(".tab-text").css("margin-top", "0em");}
    
    var tabs = ["", "#features-tab", "#lab-practice-tab", "#customization-tab"];
    
    var currentTab = 1;
    var tickerLocations = [0, 0, 0, 0];
    
    openCurrentTab();
    
    var tickerWidth = $("#ticker").width();
    var margin = 20;
    var widths = [];
    
    var count = 1;
    $("#tabs").find("button").each(function(){
        widths[count] = $(this).width() + margin;
        count++;
    });
    
    $("#tabs").find("button").each(function(){
        var thisTab = setTickerLocations($(this));
        $(this).click(function(e){
            e.preventDefault();
            currentTab = thisTab;
            $("#ticker").css("margin-left", tickerLocations[currentTab] + "px");
            openCurrentTab();
        });
       
    });
    
    function toggleTabImg(buttonSelector, active){
        
        if(active){active = "_active";}
        else {active = "";}
        
        var bgImg = "url(/images/icons/tab_" + buttonSelector.attr("id").substring(3, 4) + active + ".png)";
        buttonSelector.css("background-image", bgImg);
        
    }
    
    function openCurrentTab(){
        
        // hide any that are open
        $(".tab-content").hide();
        $("#tabs").find("button").each(function(){toggleTabImg($(this), false);});
        
        // open the current tab
        toggleTabImg($("#tab" + currentTab + "-link"), true);
        $(tabs[currentTab]).fadeIn();
        
    }

    function setTickerLocations(tabLink){
        var thisTab = tabLink.attr("id").substring(3, 4);
        var fullWidth = 0;
        var tabTextSpan = tabLink.find(".tab-text").width();
        var numberImg = tabLink.width() - tabTextSpan;
        if(thisTab == 2){numberImg += 17;}
        
        var numberOfTabsBefore = thisTab-1;
        
        for(var i = 1; i <= numberOfTabsBefore; i++){
            fullWidth += widths[i];
        }
        
        tickerLocations[thisTab] = fullWidth + numberImg + tabTextSpan - 50;
        return thisTab;
    }

});
