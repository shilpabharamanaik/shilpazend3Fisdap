// accordion functionality
function initAccordion() {
	var currentlyOpen;
	
	$(".accordionHeader").each(function(){
		closeCurrent();
		
		$(this).click(function(){
			if (currentlyOpen) {
				closeCurrent();
				
				// it's not us!
				if(currentlyOpen.text() != $(this).text()){
					openCategory($(this));
				}
				else {
					// we've closed ourself, set currently open to null
					currentlyOpen = null;
				}
			} else {
				openCategory($(this));
			}
		})
	});

	function openCategory(category){
		currentlyOpen = category;
		category.addClass("selectedCategory");
		category.next().slideDown({
			"done" : function() {
				// if this is a scrolling div, scroll to the top of this category
				$(category).parent().animate({
					scrollTop: $(category).parent().scrollTop() + $(category).position().top - 36
				});
			}
		});
		category.find(".arrowImg").html("<img src='/images/accordion_arrow_down.png'>");
	}
	
	function closeCategory(category){
		category.removeClass("selectedCategory");
		category.next().slideUp();
		category.find(".arrowImg").empty();
		category.find(".arrowImg").append("<img src='/images/accordion_arrow_right.png'>");
	}

	function closeCurrent() {
		if(currentlyOpen){
			closeCategory(currentlyOpen);
		}
	}
}