/*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
*
*	Copyright (C) 1996-2011.  This is an unpublished work of
*			Headwaters Software, Inc.
*				ALL RIGHTS RESERVED
*	This program is a trade secret of Headwaters Software, Inc.
*	and it is not to be copied, distributed, reproduced, published,
*	or adapted without prior authorization
*	of Headwaters Software, Inc.
*
*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*/

/** Leaving code which handles textarea use
*/
function inArray(needle, haystack) {
    var length = haystack.length;
    for(var i = 0; i < length; i++) {
        if(haystack[i] == needle) return true;
    }
    return false;
}

function capitaliseFirstLetter(string)
{
    return string.charAt(0).toUpperCase() + string.slice(1);
}

var fiscom = new function() {
	this.urlGetData = '/skills-tracker/comments/get-comments';
	this.urlSaveComment = '/skills-tracker/comments/save-comment';
	this.urlDoneCommenting = '/skills-tracker/comments/done-commenting';
	this.urlDeleteComment = '/skills-tracker/comments/delete-comment';
	
	// performance options
	this.animateOn = false; // todo - not working now
	
	// jquery selectors
	this.selDialogId = 'commenting-dialog-container';	// was:jquery-dialog-container
	this.selDialog = 'div#' + this.selDialogId;
	
	// add a comment form options	
	this.tfu = false; 	// Add a comment form: TextareaFormUsed vs full form used
	this.aacSaveSel = 'button#comment-save-button';
	this.aacCancelSel = 'button#comment-cancel-button';	
	this.aacFormSel = 'div#add-comment-form';
	this.aacComment = 'textarea#comment-textarea';
	this.aacLabelAddOrEdit = 'label.comment-label';
	this.aacInstructorOnly = 'input#comment-instructor-only';
	
	// comment editing
	this.editingOn = false;
	this.diagTitleHeight = 50;	// used to calculate position of inner dialog divs
	this.newComment=''			// value of not yet saved new comment while editing
	
	var oneClickTimeout;
	
	//this.addACommentForm = '';
	
	// behavior settings
	// currentMode = 'modal';
	
	this.commentPageLinks = {
	//	//'shifts' => 
	};
	
	this.cCurrent=0;	// current selector, 0 (false) means: adding new comment
	this.cNick='';
	
	this.curUserNick='';
	this.curUserIsInstructor = false;
	this.instructorOnlyCommentsPresent = false; // controls legend 'highlighting'
	
	// status icons
	this.imagesDir='/images/icons/';
	this.imgStatusHasComments='comment.svg';
	this.imgStatusNoComments='comment.svg';
	this.callingElement='';
	
	//this.urlDoneCommenting = '/skills-tracker/commenting/done-commenting';
	this.commentsSaved = [];
	this.tm = 0; // test mode
	this.commentForm = '';
		
	this.pCommPrefs = {
		animate: true,
		animationSpeed: 'fast', //'slow'/'normal'/'fast',
		maxHeight: 500
	};
	
	this.runOnLoad = function()
	{
		$(document).ready(function() {

			// add a comment save button
			$(fiscom.aacSaveSel).live("click", function() {
				fiscom.saveAndCloseComment();
			});
			
			// add a comment cancel button
			$(fiscom.aacCancelSel).live("click", function() {
				fiscom.aacCancelClicked();
			});
			
			$(fiscom.aacComment).live("keyup", function() {
				fiscom.aacCommentChanged();
			});
			
			$(fiscom.selDialogId).addClass('gray-button');
		});
	}
	
	this.runOnLoad();

	this.initFisCom=function() {
		// elementid holds id of last element
		this.dynElId = 0;
		this.dynElPrefix = 'dynCommentElement';
			
		// tracks state of shift button inside textareas
		this.shiftDown = false;
	
		// commenting vars: table, dataId, commentId, viewingUserId
		this.tb = '';		// todo: un-'mockup'
		this.dId = '';
		this.vUId='';
		
		// array of modified comment ids. They will be saved on 'Done'
		// comments we can interact with ONLY, rest is on the form
		this.c = {};
		//this.c[0] = {};
		
		this.debugTxt='';	
	}

	this.dynSel = function(i) {		//ex #dyntext1
		return '#' + this.dynEl(i);
	}
	
	this.dynEl = function(dynElId) {		//ex dyntext1
		if (typeof(dynElId)=='undefined') {
			dynElId = this.dynElId;
		}
		return this.dynElPrefix + dynElId;
	}
	
	// AJAX CALLS
	this.getData = function(tb, dId) //, vUId = logged in user
	{
		var data = {
			tb: tb,
			dId: dId,		//vUId: vUId,
			dynElPrefix: this.dynElPrefix,
			format: 'json'
		}
		$.ajax(
			{
				type: "POST",
				url: this.urlGetData,
				data: data,
				success: function(results) {
					var res=$(results)[0];
					
					// save comment data to fiscom
					fiscom.c = {};
					//fiscom.c[0] = {};
					fiscom.c = res['comments'];
					fiscom.curUserNick = res['curUserNick'];
					fiscom.curUserIsInstructor = res['curUserIsInstructor'];
					fiscom.addACommentForm = res['commentform'];

					//fiscom.commentForm = res['form'];
					//console.log(fiscom.commentForm);
					fiscom.restoreComments();
				}
			}
		)
	}
	
	this.restoreComments=function()
	{
		for (c in this.c) {
			this.restoreComment(c);
			
			// soft-deleted comment
			if (this.c[c]['deleted']) {
				this.deleteCommentShowDeleted(c);
			}
		}
		
		// instructor only legend highlighting
		if (this.curUserIsInstructor) {
			this.instructorOnlyCommentsPresent = false;
			for (c in this.c) {
				if (this.c[c]['instructor_only']) {
					this.instructorOnlyCommentsPresent = true;
				}
			}
			
			this.instructorLegendHighlight();
		}
		// add new textarea after this.dynEl = last element
		//console.log('Adding first textarea after restoring comments dynEl: '+this.dynEl())
		
		if (this.tfu) {
			var name = this.newTextarea(this.dynEl());
		} else {
			this.showCommentForm(this.dynEl());
		}

		// add blank textarea (careful if reusing: dynSel() changes AFTER running this fx)
		//$(this.dynSel()).after(this.newTextarea()); //append
	}
	
	this.instructorLegendHighlight = function()
	{
		if (this.instructorOnlyCommentsPresent) {
			$(this.aacInstructorOnly).parent().attr('class', 'instructor-only');
		} else {
			$(this.aacInstructorOnly).parent().attr('class', '');
		}
	}
	
	this.showCommentForm=function(befEl) {
		if (this.currentMode == 'page') {
			$(this.addACommentForm).appendTo(this.selDialog);
			
			// same cancel button is also back button if page mode
			$(this.aacCancelSel).css('visibility', 'visible').show();			
		} else {
			this.moveAddACommentForm(this.selDialog);
		}
		this.retrieveAddACommentForm();
		
		// if not instructor hide 'only instructors can see this comment'
		if (!this.curUserIsInstructor) {
			$(this.aacInstructorOnly).parent().hide(); // css('display', 'none');
		}
	}
	
	this.restoreComment=function(c) {
		// hide instructor only comment if not instructor (redundant)
		if(this.c[c]['instructor_only'] && !this.curUserIsInstructor) {
			this.c[c]['comment'] = '';
			return;
		}
		
		var debug_befDynElId = this.dynElId;
		var heading='';
		var headingCreated='';
		var headingCreatedDate='';
		var headingCreatedTime='';
		var headingUserNick='';
		
		// demoonly
		var wantToEdit = false;	//this.c[c]['editable']
		
		if(wantToEdit) {
			this.addTextarea(this.dynEl(), this.c[c]['cId']);
			$("#"+c).text(this.c[c]['comment']);
		} else {
			this.addTextOnlyComment(this.dynEl(), this.c[c]['cId']);
			$("#"+c+"-body").text(this.c[c]['comment']);
			
			headingCreated=this.c[c]['created'];
			headingCreated=this.c[c]['createdDate'] + '<br/>' + this.c[c]['createdTime'];
			if (this.tm) {
				headingCreated += ' ' + this.c[c]['cId'] + ' ' + this.c[c]['editable'] + ' Del?'+this.c[c]['deletable'];
			}
			headingUserNick=this.c[c]['uNick'];
			$("#"+c+"-heading-created").html(headingCreated);
			$("#"+c+"-heading-user-nick").text(headingUserNick);
			
			if(this.c[c]['deletable']) {
				this.addDeleteOption(c);
			}
			
			if(this.editingOn && this.c[c]['editable']) {
				this.addEditOption(c);
			}
		}
	}
	
	this.aacCommentChanged = function() {
		if(this.unsavedChanges()) {
			$(this.aacSaveSel).attr("disabled", false);
		} else {
			$(this.aacSaveSel).attr("disabled", true);
		}
	}
	
	this.addDeleteOption = function (id) {
		var deleteOption = '<div class="delete-comment">'
			+ '<a href="#" onClick="fiscom.deleteComment(\''+id+'\'); return false;">'
			+ '<img class="small-icon" src="/images/icons/delete.png">'
			+ '</a></div>';
		$("#"+id).append(deleteOption); //+'-comment-outer-div'
	}

	this.addEditOption = function (id) {
		var editOption = '<div class="edit-comment small-link">'
			+ '<a href="#" onClick="fiscom.editComment(\''+id+'\'); return false;">Edit</a>'
			+ '</div>';
		$("#"+id).append(editOption); //+'-comment-outer-div'
		//$("#"+c+" .edit-comment")		+ '" onClick="fiscom.textareaClicked(\'' + name + '\');'
	}

	this.deleteComment = function (id) {
		var comment = $("#"+id+"-body").text();
		
		//$("#"+id).html('<div class="comment-body"><div class="comment-body-deleted">Comment Deleted</div></div>');

		if (this.c[id]['deletable']) {
			var data = {
				tb: this.tb,
				cId: this.c[id]['cId'],
				dId: this.dId,
				dynElPrefix: this.dynElPrefix,
				format: 'json',
				requestedAction: 'delete'
			}
			$.ajax(
				{
					type: "POST",
					url: this.urlDeleteComment,
					data: data,
					success: function(results) {
						var res=$(results)[0];
						
						var success=res['success'];
						fiscom.deleteCommentShowDeleted(id);
						fiscom.c[id]['deleted'] = true;
					}
				}
			)
		}
	}

	// display delete, also used when loading comments
	this.deleteCommentShowDeleted = function (id) {
		
		this.c[id]['undelete'] = $("#"+id).html();
		
		// deletable / undeletable content of 'deleted' comment:
		var deletedContent = '<div class="comment-body-deleted">Comment Deleted';
			deletedContent += ' by ' + this.c[id]['uNick'];
		if (this.c[id]['deletable']) {
			deletedContent += '&nbsp; &nbsp; <a href="#"'
				+ ' onClick="fiscom.undeleteComment(\''+id+'\'); return false;">Undo</a>';
		}
		deletedContent += '</div>';
		
		$("#"+id).html(deletedContent);
	}
	
	this.undeleteComment = function (id) {
		var comment = $("#"+id+"-body").text();
		
		// display undelete
		$("#"+id).html(this.c[id]['undelete']);
		this.c[id]['undelete'] = '';
		var html = '<div class="comment-body-deleted">Comment UnDeleted'
			+ '&nbsp; &nbsp; <a href="#" onClick="fiscom.undeleteComment(\''+id+'\'); return false;"></a>'
			+ '</div>';
		//$("#"+id).html('<div class="comment-body"><div class="comment-body-deleted">Comment Deleted</div></div>');

		if (this.c[id]['deletable']) {
			var data = {
				tb: this.tb,
				cId: this.c[id]['cId'],
				dId: this.dId,
				dynElPrefix: this.dynElPrefix,
				format: 'json',
				requestedAction: 'undelete'
			}
			$.ajax(
				{
					type: "POST",
					url: this.urlDeleteComment,
					data: data,
					success: function(results) {
						var res=$(results)[0];
						
						var success=res['success'];
						fiscom.c[id]['deleted'] = false;
					}
				}
			)
		}
	}
	
	this.fadeAllExcept = function (value, except) {
		for (c in this.c) {
			if(c!=except) {
				$('div#'+c).fadeTo(500, value);
					//.hover(function () {
					//	$(this).fadeTo(500, 1);
					//}, function () {
					//	$(this).fadeTo(500, 0.2);
					//});
			}
		}
	}
	
	// Start editing
	this.editComment = function(id) {
		if(!this.editingOn) {
			return;
		}
		
		if (this.tfu) {
			this.textareaClicked(id);
			return;
		// any reasons NOT to edit?
		} else {
			var msg='';
			if (typeof(this.c[id])=='undefined') {
				msg='Comment ' + id + ' undefined';
			}
			if (!this.editingOn) {
				msg='Editing is disabled';
			}
			if (this.cCurrent!=0) {
				// toggle between last comment and previously cancelled one
				//	(if currently edited comment's 'Edit' link is clicked)
				if(this.cCurrent==this.c[id]['cId']) {
					if (typeof(this.c[id]['aborted_comment'])!='undefined') {
						var curComment=$(this.aacComment).val();
						
						if(curComment==this.c[id]['comment']) {
							$(this.aacComment).val(this.c[id]['aborted_comment']);
						} else if (curComment==this.c[id]['aborted_comment']) {
							$(this.aacComment).val(this.c[id]['comment']);
						}
					}
				}
				msg='Other comment ('+this.cCurrent+') is already being edited';
			}
			if (msg) {
				//c onsole.log('Aborting comment edit '+id+' '+msg);
				return;
			}
			
			if (this.tm) {
//				console.log(this.c[id]);
			}
		}
		
		// start editing is official
		this.cCurrent=this.c[id]['cId'];
		
		// save new comments value
		this.newComment = $(this.aacComment).val();
		//this.c[this.dynEl(this.cCurrent)]['comment'] = $(this.aacComment).val();
		
		// restore aborted comment if applicable
		if (typeof(this.c[id]['aborted_comment'])=='undefined') {
			$(this.aacComment).val(this.c[id]['comment']);
		} else {
			$(this.aacComment).val(this.c[id]['aborted_comment']);	//this.c[this.dynEl(this.cCurrent)]['aborted_comment'] = $(this.aacComment).val();
		}
		
		// disable other edit links
		//$('div.edit-comment').hide();
		//$('div.edit-comment').attr('disabled', 'disabled'); //hide();
		
		// hide static comment
		//$('div#' + id).hide();
		this.fadeAllExcept(0.25, id);
		
		if (this.animateOn) {
			// todo make it work:
			//var fromY=$(this.aacFormSel).position().top;
			//var toY=$(this.aacFormSel).position().top;
			//var thisMuch=(fromY-toY);
			//console.log('Will Animate '+thisMuch+ ' from/to:'+fromY+'/'+toY);
			//$(this.aacFormSel).animate({
			//	top: '-=300px;' ////thisMuch
			//}, 1000, function() {
			//	$('div#'+id).after($(this.aacFormSel));
			//});
		} else {
			$('div#'+id).after($(this.aacFormSel));
		}
		
		// change label to edit
		$(this.aacLabelAddOrEdit).text('Edit comment');
		
		// enable cancel button
		$(this.aacCancelSel).css('visibility', 'visible').show();
		
		// modal: scroll to top of comment
		if (this.currentMode == 'modal') {
			var diagPos = this.getDiagPosition();
			
			var divTop=$('div#'+id).position().top;
			var posTo=$('div#'+id).position().top + diagPos[1] + this.diagTitleHeight;
			var windowScrollTop=$(window).scrollTop();
			var documentScrollTop=$(document).scrollTop();
			var added=diagPos[1]+divTop;
			//console.log('divTop:'+divTop+' diagPos[1]:'+diagPos[1]+' added: '+added+' windowScrollTop:'+windowScrollTop+' documentScrollTop:'+documentScrollTop+' posTo'+posTo);
			$(window).scrollTop(posTo);
		}
	}
	
	// working well for y dimension only
	this.getDiagPosition = function()
	{
		var diagPos = $(this.selDialog).dialog( "option", "position" );
		
		if (typeof(diagPos)=='string') {
			diagPos=[];
			diagPos[0] = 0;	// todo implement
			diagPos[1] = 0;	// for 'top'
		}
		
		return diagPos;
	}

	/**
	 *	Two completely diferent cancel functionalities are handled here:
	 *		1. Cancelling of single comment editing
	 *		2. Cancelling of whole comment form in 'page' mode
	 */
	this.aacCancelClicked = function()
	{
		if (this.cCurrent==0) {
			if (this.currentMode == 'page') {	// for cleaniness/to avoid future weird issues
				this.redirectBack();
			} else {
				alert ("Unexpected action. Error COM101");
			}
		} else {
			// in single comment edit mode: cancelling an edit
			var cId=this.dynEl(this.cCurrent);
			this.c[cId]['aborted_comment'] = $(this.aacComment).val();
			this.aacStopEditingComment();
		}
	}
	
	// takes back to the standard 'back link' used in skills-tracker layout file
	this.redirectBack = function()
	{
		var link = $('a.page-title-link').first().attr('href');
		
		if (typeof(link)!='undefined'){
			if (this.currentMode=='page'){
				if (history.length==1) {	// was it open in new tab?
					window.close();
				} else {					// was it open in same page?
					window.location = link;
				}
			}
		}
	}
	
	this.aacStopEditingComment=function()
	{
		// empty comment box, but save value before
		var cId=this.dynEl(this.cCurrent);
		
		if (this.cCurrent!=0) {
			// check if changes were made not needed because user can go back
			//	to the same comment edit and get back changes
			
			// hide cancel button
			if (this.currentMode != 'page') {
				$(this.aacCancelSel).hide();
			}
			
			//$('div#' + id).after($(this.aacFormSel)); remove div
			this.moveAddACommentForm(this.selDialog);	//$(this.aacFormSel).appendTo(insideThis);
			
			// restore previous comment value
			$(this.aacComment).val(this.newComment);
			
			// change label to edit
			$(this.aacLabelAddOrEdit).text('Add a comment');
			
			// un-fade other comments
			this.fadeAllExcept(1, cId);
			
			// leave editing mode
			this.cCurrent=0;
		}
	}
	
	this.saveAndCloseComment = function(id, addNew) {
		
		// by default add new comment
		if (typeof(addNew=='undefined')) {
			addNew=true;
		}
		
		if (this.tfu) {
			// comment value
			var comment = $('#'+id).val();
			
			if (comment!='') { 
				this.saveComment(id, addNew);
			}
		} else {
			var elSel = this.dynElPrefix + this.cCurrent;
			
			this.saveComment(elSel, addNew);
		}
		
	}
	
	this.saveAndCloseCommentAfterAJaxCall = function(id, addNew, oldId)
	{
		if (this.tfu) {
			// don't add new if not last comment on list
			var isTextarea=$('textarea#'+id).length;
			var dynEl = this.dynEl();
			
			if(dynEl != id) {
				addNew=false;
				//console.log('Disabling adding new textarea. id:'+id+' dynEl: '.dynEl);
			}
			
			// add new comment
			if (addNew) {
				name = this.newTextarea(this.dynEl());
			}
		} else {
			
			// display new saved comment - look: this.restoreComments
			if (this.cCurrent) {
				// update new value
				cId=this.dynEl(this.cCurrent);
				$('div#'+cId+'-body').html($(this.aacComment).val());
				
				this.aacStopEditingComment();
				
				this.highlightAndFadeOut(cId);
				
			} else {
				// display just saved comment
				this.restoreComment(id);
				
				// clear saved value of new comment
				this.newComment='';
				
				$(this.aacComment).val('');
				
				// instructor only legend highlighting
				if (this.curUserIsInstructor && !this.instructorOnlyCommentsPresent && this.c[id]['instructor_only']) {
					this.instructorOnlyCommentsPresent = true;
					this.instructorLegendHighlight();
				}
			}
		}
	}

	this.highlightAndFadeOut=function(cId)
	{
		//todo highlight then fade just edited comment
		//ex:	http://stackoverflow.com/questions/1757988/highlight-then-fade-highlight-for-list-items-dynamically-added-to-a-list
		//		http://docs.jquery.com/UI/Effects/Highlight
		//cId=this.dynEl(this.cCurrent);
		$('div#'+cId).fadeTo(1, 0.2).fadeTo(4000, 1);
	}

	// looks through all comments, saves any unsaved ones
	// use before closing comment window
	this.saveAllUnsaved = function(quiet)
	{
		if(typeof(quiet=='undefined')) {
			quiet=true;
		}
	}
	
	// saves comment for textarea id=id or this.cCurrent
	this.saveComment = function(id, alsoAddAnother)
	{
		// default also Add Another comment
		if(typeof(alsoAddAnother=='undefined')) {
			alsoAddAnother=true;
		}
		
		cId='';
		
		if (this.tfu) {
			var el=$("#"+id);
			var val = $("#"+id).val();
		} else {
			var val = $(this.aacComment).val();
			
			if (this.cCurrent) { // edit comment mode
				cId = this.c[id]['cId'];
							
				if (this.c[id]['cId']!=this.cCurrent) {
					//console.log('PROBLEM!!! wrong value passed to function (id):'+id);
					return;
				}
			}
			
			if (!val) {
				alert ('Nothing to save..');
				return;
			}
			
			this.c[id] = {};
			//this.c[id]['cId'] = ''; // todo: correct value for blank comment???
			
			// todo: also send/save who to email
		}
		var instructorOnly = $(this.aacInstructorOnly).is(':checked');
		
		var contacts = [];
		
		$('#staggered-list-parent input[type="checkbox"]').each(function(index, el){
			if($(el).is(':checked')){
				contacts.push($(el).attr('id'));
			}
		});
		
		var data = {
			tb: this.tb,
			dId: this.dId,
			cId: cId,	//this.c[id]['cId']
			comment: val,
			format: 'json',
			instructor_only: instructorOnly,
			toEmail: contacts
		}
		
		$.ajax(
			{
				type: "POST",
				url: this.urlSaveComment,
				data: data,
				success: function(results) {
					var res=$(results)[0];
					//console.log(res['debug']);
					
					var newId = fiscom.dynElPrefix + res['cId'];
					if(typeof(fiscom.c[newId])=='undefined') {
						fiscom.c[newId] = {};
					}
					
					// integrity check for edited comments					
					if(fiscom.cCurrent!=0) {
						if (id!=newId) {
							alert('Unexpected error occured. (Ids not matching: id given:'+id+'\nID received:'+newId);
						}
					}
					
					// set return values
					fiscom.c[newId]['cId']=res['cId'];
					fiscom.c[newId]['created']=res['created'];
					fiscom.c[newId]['createdDate']=res['createdDate'];
					fiscom.c[newId]['createdTime']=res['createdTime'];
					fiscom.c[newId]['uNick']=res['uNick'];
					fiscom.c[newId]['comment']=res['comment'];
					fiscom.c[newId]['editable']=res['editable'];
					fiscom.c[newId]['deletable']=res['deletable'];
					fiscom.c[newId]['uId']=res['uId'];
					fiscom.c[newId]['instructor_only']=res['instructor_only'];
					
					fiscom.saveAndCloseCommentAfterAJaxCall(newId, alsoAddAnother, id);
					fiscom.recordCommentSaved(res['cId']);
				}
			});
	}
	
	this.recordCommentSaved = function(id)
	{
		var lastId = this.commentsSaved.length;
		
		// @todo check for duplicate values. if only js wasn't so dumb about arrays
		
		// keep track of saved comments
		var found = false;
		for(var i = 0; i < lastId; i++) {
			if(this.commentsSaved[i] == id) {
				found = true;
			}
		}
		if (!found) { 	// doesn't work: inArray(id, this.commentsSaved)
			this.commentsSaved[lastId] = id;
		}
	}
	
	// create container if it doesn't exist, without arguments creates jquery dialog div
	this.createDialogContainerIfNeeded = function(selDialogId, selDialog) {
		// set defaults for selDialog & selDialogId
		if (typeof(selDialogId)=='undefined') {
			selDialogId = this.selDialogId;
		}
		if (typeof(selDialog)=='undefined') {
			selDialog = 'div#' + selDialogId;
		}
		
		var containerExists=($(selDialog).length > 0);
		if (!containerExists) {
			// before footer if exists
			footer = $('#'+'footer');
			if (footer) {
				$(footer).before('<div id="' + selDialogId + '"></div>');
			} else {
				$("body").append('<div id="' + selDialogId + '"></div>');				
			}
		}
	}
	
	// entry point from forms
	/**
	 *	@param tb string	supported table
	 *	@param dId integer	data id
	 *	@param callingElement domElement of calling element
	 *	@param currentMode string, one of:
	 *		modal	(default)
	 *		link	link to page instead of modal
	 *		linkNewTab
	 *		page	comments on their own page
	 */
	this.startCommenting = function(tb, dId, callingElement, currentMode)
	{
		// prevent double clicks
		if (this.oneClickLock()) {
			return;
		}
		
		this.initFisCom();
		
		if (typeof(currentMode)=='undefined') {
			currentMode = 'link';
			//currentMode = 'modal';
		}
		this.currentMode = currentMode;
		
		this.callingElement=callingElement;
		this.tb = tb;
		this.dId = dId;
		
		if (currentMode == 'modal' || currentMode == 'page') {
			this.initCommenting(tb, dId, callingElement);
		} else if (currentMode == 'link' || currentMode == 'linkNewTab') {
			windowName = tb + '_' + dId;
			
			pathArray = window.location.pathname.split( '/' );
			
			// default link
			if (typeof(this.commentPageLinks[tb]) == 'undefined') {
				path = window.location.pathname;
				
				this.commentPageLinks[tb] = path.substring(path.indexOf('/')) + '/comments';
			}
			
			// comments in same window vs new tab
			var loc = this.commentPageLinks[tb] + '/id/' + dId, windowName;
			if (currentMode == 'link') {
				window.location = loc;
			} else {	// new tab
				window.open(loc);
			}
		}
	}
	
	this.initCommenting = function(tb, dId, callingElement)
	{
		this.callingElement=callingElement;
		this.tb = tb;
		this.dId = dId;
		
		this.createDialogContainerIfNeeded();
		
		// gets comments and form
		this.getData(tb, dId);
		
		var content='';
		
		//$("html,body").animate({ scrollTop: 0 }, 200);
		
		content = this.contentMakeForm();
		
		if (this.currentMode == 'modal') {
			// scroll to top of page:
			window.scroll(0,0);
			
			var viewportHeight = $(window).height();   // returns height of browser viewport
			var viewportWidth = $(window).width();   //$(document).height();
			
			var calculatedWidth = 980;
			if (viewportWidth<calculatedWidth) {
				calculatedWidth = viewportWidth;
			}
			
			$(this.selDialog).dialog({
				autoOpen: false,
				width: calculatedWidth,
				modal: true,
				//resize: 'auto',
				position:'top',	//center
				buttons: {
					//"Cancel": function() {	$(this).dialog("close"); },
					'Close': function() {
						$(this).dialog('close');
					}
				},
				//open: function(event, ui) { $(this).parent().css('position','fixed');}
				beforeClose: function(event, ui) {
					return fiscom.dialogClosing(event, ui);
				},
				closeOnEscape: false
			});
			
			// center dialog
			$(this.selDialog).dialog('open')
				.dialog('option', 'title', 'Comments'); //.dialog('option', 'position', 'center');
			
			//this.addDebugInfo();
			var outHeight = $(this.selDialog).outerHeight();
			var outWidth = $(this.selDialog).outerWidth();
			
			var height = $(this.selDialog).dialog( "option", "height");
			var width = $(this.selDialog).dialog( "option", "width" );
		} else {
			// margin of comments containter in y
			$(this.selDialog).css('marginLeft', '60px').css('marginTop', '100px');
		}
		$(this.selDialog).html(content);
		
		fiscom.aacCommentChanged();
		//alert ('Viewport height/width: ' + viewportHeight + '/' + viewportWidth + '<br/>Dialog height/width: ' + height + '/'+width+'<br/>Outer h/w: '+outHeight+'/'+outWidth);
	}


	// todo test this:
	this.commentingDialogAutoResize = function()
	{
		$(this.selDialog).dialog(
			'resize', 'auto'
		);
	}
	
	this.dialogClosing = function(event, ui)
	{
		var confirmation = true;
		
		// is comment editing active?
		//	argument=true only prevents warning window when user changes comment,
		//	cancells change, hits edit again on the same comment
		//	(brings up unsaved changes he/she cancelled already) and closes window
		if (this.unsavedChanges(true)) {
			if (this.cCurrent==0) {
				confirmation = confirm("Your changes are not saved.\n\nAre you sure you want to abandon them ?");
			} else {
				confirmation = confirm("You're editing comment.\n\nAre you sure you want to abandon changes?");
			}
		}
		
		if (confirmation) {
			this.saveAllUnsaved();
			this.doneCommenting(this.dId);
			this.refreshCallingCommentIcon();
			this.returnAddACommentForm();
			this.newComment='';
			$(this.aacComment).val('');
			this.instructorOnlyCommentsPresent = false;
			this.c = {};
			this.cCurrent=0;
			fiscom.dId = '';
			
			// double click prevention - unlock
			this.unlockOneClickOnly();
			//clearTimeout(oneClickTimeout);
			return true;
		} else {
			return false;
		}
	}
	
	// will do if this.callingElement was specified
	this.refreshCallingCommentIcon = function() {
		if(!this.callingElement) {
			return;
		}

		// set icon 
		var img = $(this.callingElement).children('img')[0]; //$(this.callingElement).parent().children('img');
		if (img) { // found img tag to change
			count = this.countActiveComments();
			
			var newImgLink='';
			if (count) {
				newImgLink = this.imagesDir + this.imgStatusHasComments;
			} else {
				newImgLink = this.imagesDir + this.imgStatusNoComments;
			}
			$(img).attr('src', newImgLink);			
		} else {
			// no tag to change
		}
	}
	
	this.countActiveComments = function() {
		var cActive = 0;
		var cDeleted = 0;
		var cEmpty = 0;
		
		for (c in this.c) {
			if (this.c[c]['comment']) {
				if (this.c[c]['deleted']) {
					cDeleted++;
				} else {
					cActive++;
				}
			} else {
				cEmpty++;
			}
		}
		//console.log("Active:"+cActive+" Deleted:"+cDeleted+" Empty:"+cEmpty);
		return cActive;
	}
	
	this.unsavedChanges = function(alsoConfirmedDiscards) {
		// check for changes when editing
		var val=$(this.aacComment).val();
		
		var changed=true;
		if (this.cCurrent==0) {
			var changed=(val!='');
		} else {
			var id=this.dynEl(this.cCurrent);
			
			// has user confirmed discard of current value
			if(typeof(alsoConfirmedDiscards)=='undefined') {
				alsoConfirmedDiscards=false;
			}
			if (alsoConfirmedDiscards && typeof(this.c[id]['aborted_comment'])!='undefined') {
				if (val==this.c[id]['aborted_comment']) {
					changed=false;
				}
			}
			if (val==this.c[id]['comment']) {
				changed=false;
			}
		}
		return changed;
	}

	// add a comment methods (aac)
	this.moveAddACommentForm = function(insideThis)
	{
		$(this.aacFormSel).appendTo(insideThis);
	}
	
	// moves form from outside of a jquery dialog
	this.retrieveAddACommentForm = function(insideThis)
	{
		if (this.tfu) {
			return;
		}
		
		// make form visible
		$(this.aacFormSel).css('display', 'inline');
		//$('div#educators_list').css('display', 'block');
		this.commentingDialogAutoResize();
	}
	
	// moves form back to outside of jquery dialog
	this.returnAddACommentForm = function()
	{
		if (this.tfu) { // full form not used
			return;
		}
		
		$(this.aacFormSel).css('display', 'none');
		$(this.aacFormSel).appendTo('body');
	}
	
	this.doneCommenting = function(dId)
	{
		if (this.cCurrent!=0) {
			this.aacCancelClicked();
		}
		
		var data = {
			tb: this.tb,
			dId: dId,
			cIds: this.commentsSaved,
			format: 'json'
		}
		
		$.ajax(
			{
				type: "POST",
				url: this.urlDoneCommenting,
				data: data,
				success: function(results) {
					var res=$(results)[0];
				}
			});
	}
	
	// content functions
	this.contentMakeForm = function() {
		var content = '';
		this.dynElId++;
		content += '<input type="hidden" name="commenting-top-anchor" value="" id="' + this.dynEl() + '">'
		content += '</form>';
		return content;
	}
	
	this.newTextarea = function(beforeSelector, name, value) {
		//name = typeof(name) != 'undefined' ? name : ;
		name = this.addTextarea(beforeSelector, name, value);
		
		//var content = this.getTextareaHtml(name);
		this.c[name] = {};
		this.c[name]['dynElId']=this.dynElId;
		this.c[name]['cId'] = null;
		return name;
	}
	
	// creates comment, new or using existing data
	// @todo: option to make sub-comment
	// @todo: set value
	this.addTextarea = function(beforeSelector, elId, value) { //,subComment
		// user requested elId needs special treatment
		if (typeof(elId)!='undefined') {
			var name=this.dynElPrefix+elId;
			this.dynElId=elId;
			//a lert ('AddTextArea: UserMode: beforeSelector:'+beforeSelector+' elId: '+elId + ' name:'+name+' dynElId:'+this.dynElId);
		}
		
		var el=this.getTextareaHtml(name);
		//$("#debug-textarea").text(el);
		$("#"+beforeSelector).after(el); //append
		
		var name=typeof(elId=='undefined')? this.dynEl() : elId;
		
		// enable pretty comments
		$('#'+name).prettyComments(this.pCommPrefs);
		
		// @todo set value 
		if(typeof(value)!='undefined') {
			
		}
		return name;
	}
	// adds text area only
	this.getTextareaHtml = function(name) {
		if (typeof(name)=='undefined') {
			this.dynElId++;
			name=this.dynEl();
		}
		var content = '\n<textarea cols=79 rows=2 id="' + name + '" name="' + name
			+ '" return onKeyup="fiscom.textareaOnKeyUp(\'' + name + '\', event);'
			+ '" return onKeydown="fiscom.textareaOnKeyDown(\'' + name + '\', event);'
			+ '" onClick="fiscom.textareaClicked(\'' + name + '\');'
			+ '">\n';
		return content;
	}
	
	// complement to addTextarea
	this.addTextOnlyComment = function(beforeSelector, elId) {
		if (typeof(elId)!='undefined') {
			var name=this.dynElPrefix+elId;
			this.dynElId=elId;
		}
		var el=this.getTextOnlyCommentHtml(name);
		$("#"+beforeSelector).after(el);
	}
	
	this.getTextOnlyCommentHtml = function(name) {
		if (typeof(name)=='undefined') {
			this.dynElId++;
			name=this.dynEl();
		}

		// styling of hidden (instructor-only) comment
		var instructorOnlyClass = '';
		if (!(typeof(this.c[name])=='undefined') && this.c[name]['instructor_only']) {
			instructorOnlyClass = ' instructor-only';
		}

		var content = '\n<div class="comment-outer-div' + instructorOnlyClass + '" id="' + name + '" name="' + name + '"'
		//var content = '\n<div class="'+instructorOnlyClass+' comment-outer-div" id="' + name + '" name="' + name + '"'
			//+ '" onClick="fiscom.textareaClicked(\'' + name + '\');'
			+ '">'
			+ '<div class="comment-heading" id="'+name+'-heading">'
			+ '<div class="comment-heading-user-nick" id="'+name+'-heading-user-nick"></div>'
			+ '<div class="comment-heading-created" id="'+name+'-heading-created"></div>'
			+ '</div>'
			+ '<div class="comment-body" id="'+name+'-body"></div>'
			+ '<br/></div>\n';
		return content;
	}
	
	
	this.textareaClicked = function(id) {
		//this.addTextarea(id);
		// $("#"+id).remove();
		
		var isTextarea=$('textarea#'+id).length;
		var comment='';
		if(isTextarea){
			comment = $('textarea#'+id).val();
		} else {
			comment = $("#"+id+"-body").text();
			
			if (this.c[id]['editable']) {
				this.commentToggleTextTextarea(id);
				//this.commentToText(id);				
			}
		}
	}
	
	// text => textarea and vice-versa
	this.commentToggleTextTextarea=function(id, convertOnlyTo) {
		
		// what to convert to
		var okToText = true;
		var okToTextarea = true;
		if (typeof(convertOnlyTo)!='undefined') {
			if(convertOnlyTo=='text') {
				okToTextarea = false;
			} else if (convertOnlyTo=='textarea') {
				okToText = false;
			}
		}
		
		var idExists=($('#'+id).length > 0);
		var isTextarea=$('textarea#'+id).length;
		
		var addBefore ='';
		// to text
		if (isTextarea && okToText) {
			prevId = $('textarea#'+id).prev().attr('id');
			sel = this.addTextOnlyComment(prevId);
		}
		
		// to textarea
		if (!isTextarea && okToTextarea) {
			prevId = $('#'+id).prev().attr('id');
			
			sel = this.addTextarea(prevId);
			// copy values
			var comment=$("#"+id+"-body").text();
			$('#'+sel).val(comment);
			$('#'+id).remove();
			this.c[sel] = {};
		}
		// add content
		if (addBefore != '') {
			$('#'+id).before(addBefore);
			$('#'+id).remove();
		}
	}
	
	this.commentToText=function(id)
	{
		this.commentToggleTextTextarea(id, 'text');
	}
	
	this.commentToTextarea=function(id)
	{
		this.commentToggleTextTextarea(id, 'textarea');
	}
	
	this.textareaOnKeyDown = function(id, e) {
		//var unicode=e.keyCode? e.keyCode : e.c harCode
		var unicode=e.keyCode;
		
		// detect enter / shift enter
		if(unicode==16) {
			this.shiftDown=true;
			//console.log('Shift key pressed');
		}
		if(unicode==13 && !this.shiftDown) { // e.shiftKey
			e.preventDefault(); // don't record enter key
		}
		return true;
	}
	this.textareaOnKeyUp = function(id, e) {
		//var unicode=e.keyCode? e.keyCode : e.c harCode
		var unicode=e.keyCode;
		
		// detect enter / shift enter
		if(unicode==13 && this.shiftDown==false) {
			this.saveAndCloseComment(id);
		}
		if(unicode==16) { // shift key released
			this.shiftDown=false;
		}
		return true;
	}
	
	/**
	 *	Prevent user from 'double-clicking'
	 */
	this.oneClickLock = function() {
		if (!this.oneClickLockFlag) {
			oneClickTimeout = setTimeout("fiscom.oneClickLockFlag = false;", 7000);
			this.oneClickLockFlag = true;
			return false;
		}
		return true;
	}
	
	this.unlockOneClickOnly = function() {
		this.oneClickLockFlag = false;
		clearTimeout(oneClickTimeout);
	}
	
	this.debugMsg=function(title, text){
		this.debugTxt += '<b>' + title + '</b>' + text + "\n";
	}
	
	this.addDebugInfo=function()
	{
		// add textarea after last dynamic field
		$(this.dynSel()).after('<textarea cols=100 rows=2 id="debug-textarea" onClick="fiscom.textareaClicked(\'debug-textarea\');">'); //append
		$("#debug-textarea").text(this.debugTxt).prettyComments(this.pCommPrefs);
	}
}