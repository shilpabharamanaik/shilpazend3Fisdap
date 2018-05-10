$(document).ready(function(){
 var cookie = $.cookie("hidden");
 var hidden = cookie ? cookie.split("|").getUnique() : [];
 var cookieExpires = 7; // cookie expires in 7 days, or set this as a date object to specify a date

 // Remember content that was hidden
 $.each( hidden, function(){
  var pid = this; //parseInt(this,10);
  $('#' + pid).hide();
  //$('#advanced_search-toggle').val(false);
  //$("#content-footer div[name='" + pid + "']").addClass('add');
 })

 // Add Click functionality
 //$('.portlet').click(function(){
 // $(this).hide();
 // $("#content-footer div[name=" + this.id + "]").addClass('add');
 // updateCookie( $(this) );
 //});
 $("#advanced_search_toggle").click(function(event){
  //$(this).toggleClass('add');
  var el = $("div#advanced_search_content");
  var toggle = $('#advanced_search-toggle');
  event.preventDefault();
  el.toggle("medium");
  //el.toggle("medium", function(){
  //  if (el.is(':visible')) {
  //      toggle.val(true);
  //  } else {
  //      toggle.val(false);
  //  }
  //});
  updateCookie( el );
 });

 // Update the Cookie
 function updateCookie(el){
  var indx = el.attr('id');
  var tmp = hidden.getUnique();
  if (el.is(':hidden')) {
   // add index of widget to hidden list
   tmp.push(indx);
  } else {
   // remove element id from the list
   tmp.splice( tmp.indexOf(indx) , 1);
  }
  hidden = tmp.getUnique();
  $.cookie("hidden", hidden.join('|'), { expires: cookieExpires } );
 }
}) 

// Return a unique array.
Array.prototype.getUnique = function() {
 var o = new Object();
 var i, e;
 for (i = 0; e = this[i]; i++) {o[e] = 1};
 var a = new Array();
 for (e in o) {a.push (e)};
 return a;
}

// Fix indexOf in IE
if (!Array.prototype.indexOf) {
 Array.prototype.indexOf = function(obj, start) {
  for (var i = (start || 0), j = this.length; i < j; i++) {
   if (this[i] == obj) { return i; }
  }
  return -1;
 }
}
