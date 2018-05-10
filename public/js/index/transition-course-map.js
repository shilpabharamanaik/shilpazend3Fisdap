$(document).ready(function(){
								
								var badBrowser = false;
								
								var state_colors = {};
								
								$("body").css("overflow", "hidden");							

	
								// set up the colors
								$(".state-data").each(function(){
																abbreviation = $(this).attr("data-state-abbreviation").toLowerCase();
																state_colors[abbreviation] = "#" + $(this).attr("data-state-color");
								});
								
								if ($.browser.msie) {
																if ($.browser.version <= 8) {
																								$("#non-supported-browser").show();
																								$("#tcmap").css("color", "#fff");
																								badBrowser = true;
																}
								}
								


								// locations
								// really lame that it's hard coded, I know but paths are challenging to work with and this just makes for a cleaner result (at least for the user ;)
								var state_pos = {};
								state_pos["AL"] = {top: "233px", left: "356px", ticker: "right"};
								state_pos["AK"] = {top: "99px", left: "245px", ticker: "bottom"};
								state_pos["AZ"] = {top: "191px", left: "420px", ticker: "left"};
								state_pos["AR"] = {top: "28px", left: "546px", ticker: "bottom"};
								state_pos["CA"] = {top: "152px", left: "324px", ticker: "left"};
								state_pos["CO"] = {top: "142px", left: "507px", ticker: "left"};
								state_pos["CT"] = {top: "80px", left: "500px", ticker: "right"};
								state_pos["DE"] = {top: "125px", left: "484px", ticker: "right"};
								state_pos["DC"] = {top: "134px", left: "471px", ticker: "right"};
								state_pos["FL"] = {top: "106px", left: "494px", ticker: "bottom-right"};
								state_pos["GA"] = {top: "222px", left: "390px", ticker: "right"};
								state_pos["HI"] = {top: "152px", left: "372px", ticker: "bottom"};
								state_pos["ID"] = {top: "75px", left: "415px", ticker: "left"};
								state_pos["IL"] = {top: "133px", left: "308px", ticker: "right"};
								state_pos["IN"] = {top: "128px", left: "353px", ticker: "right"};
								state_pos["IA"] = {top: "105px", left: "255px", ticker: "right"};
								state_pos["KS"] = {top: "157px", left: "590px", ticker: "left"};
								state_pos["KY"] = {top: "170px", left: "349px", ticker: "right"};
								state_pos["LA"] = {top: "78px", left: "545px", ticker: "bottom"};
								state_pos["ME"] = {top: "17px", left: "517px", ticker: "right"};
								state_pos["MD"] = {top: "121px", left: "465px", ticker: "right"};
								state_pos["MA"] = {top: "66px", left: "500px", ticker: "right"};
								state_pos["MI"] = {top: "74px", left: "359px", ticker: "right"};
								state_pos["MN"] = {top: "26px", left: "251px", ticker: "right"};
								state_pos["MS"] = {top: "46px", left: "401px", ticker: "bottom-right"};
								state_pos["MO"] = {top: "160px", left: "270px", ticker: "right"};
								state_pos["MT"] = {top: "18px", left: "497px", ticker: "left"};
								state_pos["NE"] = {top: "110px", left: "573px", ticker: "left"};
								state_pos["NV"] = {top: "116px", left: "374px", ticker: "left"};
								state_pos["NH"] = {top: "44px", left: "505px", ticker: "right"};
								state_pos["NJ"] = {top: "100px", left: "488px", ticker: "right"};
								state_pos["NM"] = {top: "206px", left: "487px", ticker: "left"};
								state_pos["NY"] = {top: "75px", left: "447px", ticker: "right"};
								state_pos["NC"] = {top: "180px", left: "428px", ticker: "right"};
								state_pos["ND"] = {top: "25px", left: "565px", ticker: "left"};
								state_pos["OH"] = {top: "121px", left: "381px", ticker: "right"};
								state_pos["OK"] = {top: "202px", left: "591px", ticker: "left"};
								state_pos["OR"] = {top: "47px", left: "353px", ticker: "left"};
								state_pos["PA"] = {top: "104px", left: "434px", ticker: "right"};
								state_pos["RI"] = {top: "75px", left: "515px", ticker: "right"};
								state_pos["SC"] = {top: "201px", left: "423px", ticker: "right"};
								state_pos["SD"] = {top: "70px", left: "568px", ticker: "left"};
								state_pos["TN"] = {top: "188px", left: "352px", ticker: "right"};
								state_pos["TX"] = {top: "68px", left: "431px", ticker: "bottom"};
								state_pos["UT"] = {top: "133px", left: "422px", ticker: "left"};
								state_pos["VT"] = {top: "43px", left: "496px", ticker: "right"};
								state_pos["VA"] = {top: "155px", left: "446px", ticker: "right"};
								state_pos["WA"] = {top: "4px", left: "366px", ticker: "left"};
								state_pos["WV"] = {top: "146px", left: "418px", ticker: "right"};
								state_pos["WI"] = {top: "55px", left: "308px", ticker: "right"};
								state_pos["WY"] = {top: "84px", left: "486px", ticker: "left"};

				
								$('#tcmap').vectorMap(
								{
																map: 'usa_en',
																backgroundColor: null,
																colors: state_colors,
																//hoverColor: '#999999',
																//selectedColor: state_colors,
																enableZoom: true,
																showTooltip: true,
																selectedRegion: null,
																borderColor: '#fff',
																borderWidth: '2',
																hoverOpacity: '0.7',
																onRegionClick: function(element, code, region)
																{
																								openPopUp(code);
																								scrollStatesList(code);
																},
																onMouseOver: function(element, code, region){
																},
																onMouseOut: function(element, code, region){
																}
								});
								
								function scrollStatesList(code) {
																removeSelectedClass();
																// send it to the previous element
																var stateAnchor = $("#state-anchor-" + code.toUpperCase());
																var bgColor = stateAnchor.attr("data-bg-color");
																var scrollToElement = stateAnchor.prev();
																
																if (scrollToElement.length == 0) {
																								scrollToElement = stateAnchor;
																}
																
																stateAnchor.addClass("selected-state-anchor").css("background-color", "#" + bgColor);
																$("#state-list").scrollTo(scrollToElement, 'slow');
								}
								
								function removeSelectedClass() {
																$(".selected-state-anchor").each(function(){
																			$(this).removeClass("selected-state-anchor");
																			$(this).css("background-color", "#fff");
																			
																			$(this).hover(function(){
																								if (!$(this).hasClass("selected-state-anchor")) {
																																$(this).css("background-color", "#eee");
																								}
																			}, function(){
																								if (!$(this).hasClass("selected-state-anchor")) {
																																$(this).css("background-color", "#fff");
																								}
																			})
																});
																
								}
								
								function openPopUp(code) {
																
																// hide all others
																$(".state-data").fadeOut();
																
																var popup = $("#popup-" + code.toUpperCase());
																
																if (badBrowser) {
																								popup.find(".ticker-wrapper").hide();
																								popup.find(".close-popup").hide();
																								popup.fadeIn().css("left", "210px").css("top", "110px").css("height", "400px").css("width", "600px");
																								popup.find(".scrolling-content").css("height", "300px");
																}
																else {
																								var top = state_pos[code.toUpperCase()].top;
																								var left = state_pos[code.toUpperCase()].left;	
																								popup.find(".ticker-wrapper").addClass("ticker-" + state_pos[code.toUpperCase()].ticker);			
																								popup.fadeIn().css("top", top).css("left", left);
																}
																

								}
								
								
								$(".close-popup").click(function(){
																$(".state-data").fadeOut();
																removeSelectedClass();
								})
								
								
							
				$(".state-anchor").hover(function(e){
							var code = $(this).attr("id").toLowerCase().split("-");
							code = code[2];
							var path = $("#jqvmap1_" + code);
							path.css("opacity", "0.8");
							path.attr("fill-opacity", "0.8");
				}, function(){
							var code = $(this).attr("id").toLowerCase().split("-");
							code = code[2];
						 var path = $("#jqvmap1_" + code);
						 path.css("opacity", "1");
								path.attr("fill-opacity", "1");
				});
							
							//console.log($("path"));
								

								
});
