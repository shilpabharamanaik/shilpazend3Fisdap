//Javascript for /reports/goal/index

$(document).ready(function(){

	studentp.activate();

	$("#student-class, #student-classyear, #student-year, #student-month, #student-type-emt, #student-type-aemt, #student-type-pmed").change(function() {
		studentp.refreshStudentList();
	});
	
	// Checkbox-style student results controls
	$('#student-filter-controls a.control-all').click(function(event) {
		event.preventDefault();
		$(studentp.checkboxStudents + ' input[type="checkbox"]').attr('checked', 'checked');
		studentp.updateStudentListField();
	});
	$('#student-filter-controls a.control-none').click(function(event) {
		event.preventDefault();
		$(studentp.checkboxStudents + ' input[type="checkbox"]').attr('checked', false);
		studentp.updateStudentListField();
	});
	
	// make one student selection work
	$('#student_select_box').change(function(){
		studentp.updateStudentListField();
	});
});

function count(c)
{
	var cnt=0;
	for (k in c) { cnt++; }
	return cnt;
}

function isEmpty(ob){
	for(var i in ob){ return false;}
	return true;
}


var studentp = new function() {
	this.sel = {};
	this.sel.ClassSection = '#student-class';
	this.sel.ClassSectionYear = '#student-classyear';
	this.sel.GradYear = '#student-year';
	this.sel.GradMonth = '#student-month';
	this.selStudents = '#student_select_box';
	this.checkboxStudents = '#student-filter-results';
	
	// select values
	this.selVal = {};
	this.selVal.ClassSection = 0;
	
	// current drop down values
	this.cVals = {};	// full arrays
	this.c = {};		// single select
	
	// last drop down values
	this.l = {}; 
	
	// hidden field with all combined students
	this.selStudentList = '#selected-students';
	
	this.months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
	this.maxStudents = 100;
	
	// constant values from backend:
	this.CERT_EMT = 1;
	this.CERT_ADV_EMT = 2;
	this.CERT_PARAMEDIC = 3;
	this.CERT_INSTRUCTOR = 4;
	this.CERT_SURG_TECH = 5;
	
	this.FULL_NAME = 1;
	this.GRADUATION_YEAR = 2;
	this.GRADUATION_MONTH  = 3;
	this.CLASS_ID = 4;
	this.CERTIFICATION_LEVEL = 5;
	
	this.activate=function()
	{
		this.runOnLoad();
		this.refreshStudentList();
	}
	
	this.runOnLoad=function()
	{
		// default values for student picker:
		if ($('#student-month').val() == '') {
			this.c.GradMonth = $('#student-month').val('all');
		} else {
			this.c.GradMonth = $('#student-month').val();
		}
		if ($('#student-year').val() == '') {
			this.c.GradYear = $('#student-year').val('all');
		} else {
			this.c.GradYear = $('#student-year').val();
		}
		if ($('#student-classyear').val() == '') {
			this.c.ClassYear = $('#student-classyear').val('all');
		} else {
			this.c.ClassYear = $('#student-classyear').val();
		}
//console.log('c.ClassYear at onload: ' + this.c.ClassYear);
		if ($('#student-class').val() == '') {
			this.c.ClassSection = $('#student-class').val('0');
		} else {
			this.c.ClassSection = $('#student-class').val();
		}
		
		// jquery 1.6: .prop('checked', true); uncheck: $('.myCheckbox').removeAttr('checked')
		if ($('#student-type-emt').is(':checked')) {
			$('#student-type-emt').attr('checked','checked');
			this.certTypeEmt = true;
		} else {
			$('#student-type-emt').attr('checked', false);
			this.certTypeEmt = false;
		}
		if ($('#student-type-aemt').is(':checked')) {
			$('#student-type-aemt').attr('checked','checked');
			this.certTypeAemt = true;
		} else {
			$('#student-type-aemt').attr('checked', false);
			this.certTypeAemt = false;
		}
		if ($('#student-type-pmed').is(':checked')) {
			$('#student-type-pmed').attr('checked','checked');
			this.certTypePmed = true;
		} else {
			$('#student-type-pmed').attr('checked',false);
			this.certTypePmed = false;
		}
	}
	
	/**
	 *	@todo: Doing it here and not in zend only not to do database work twice since we're
	 *	getting students through ajax call.
	 *	Backend doing it now because it's simpler.
	 */
	this.setStaticDropDownValues=function()
	{
		// 1. graduation years
		var html= "<option value='all' selected='selected'>All Years</option>";;
		for (y in this.gradYearsSorted) {
			year = this.gradYearsSorted[y][0];
			html += "<option value='" + year+ "'";
			if (year == this.c.GradYear) {
				html += " selected='selected' ";
			}
			html += ">" + year + "</option>";
		}
		$(this.sel.GradYear).html(html);
		
		// 2. class section years
		this.updateClassYearOptions();
		
		// 3. classSections done as dynamic now
	}
	
	// this will get all students for currently logged in (if) instructor
	this.refreshStudentList=function()
	{
		this.getStudentsIfNeeded();
		this.getPickerParams();		// sets this.c. values
		this.updateStudentList();	// sets this.l. values
	}
	
	this.getStudentsIfNeeded = function()
	{
		// if this.students has been defined and is not empty, avoid fetching more data
		if (typeof(this.students)!='undefined' && Object.keys(this.students).length > 0) {
			return;
		}
		
		var data = {}
		
                // Check if there is a form element containing a program ID
                var programId = $('input[name="picker_program_id"]').val();
		if (typeof(programId) != 'undefined' && programId > 0) {
			var postUrl = '/reports/goal/get-all-student-picker-students/programId/' + programId;
		} else {
			var postUrl = '/reports/goal/get-all-student-picker-students';
		}

		$.ajaxSetup({async:false});
		
		$.post(postUrl, data,
			function(response) {
				studentp.classSections = response.classSections;		// name, year, type
//console.log(studentp.classSections);
				studentp.classSectionYears = response.classSectionYears;
				studentp.gradYears = response.gradYears;
				studentp.gradYearsSorted = response.gradYearsSorted;
				studentp.students = response.students;
				
				studentp.setStaticDropDownValues();
			 });
	}
	
	this.getPickerParams = function()
	{
		//$("#student-class, #student-classyear, #student-year, #student-month, #student-type-emt, #student-type-aemt, #student-type-pmed")
		this.c.GradMonth = $('#student-month').val();
		this.c.GradYear = $('#student-year').val();
		this.c.ClassYear = $('#student-classyear').val();
		this.c.ClassSection = $('#student-class').val();
		//console.log('c.ClassYear (getpickerparams): ' + this.c.ClassYear);
		
		// handles first time/default values
		if (this.c.ClassSection == null) {
			this.c.ClassSection = 'all';
		}
		if (this.c.ClassYear == null) {
			this.c.ClassYear = 'all';
		}
		
		this.certTypeEmt = $('#student-type-emt').is(':checked');
		this.certTypeAemt = $('#student-type-aemt').is(':checked');
		this.certTypePmed = $('#student-type-pmed').is(':checked');
		
		// if all are unchecked behave as if all were checked
		if (this.certTypeEmt==false && this.certTypeAemt==false && this.certTypePmed==false) {
			this.certTypeEmt = true;
			this.certTypeAemt = true;
			this.certTypePmed = true;
		}
		//console. log('Picker Params: ' + this.c.ClassSection + ' ' + this.c.ClassYear + ' ' + this.c.GradYear + ' ' + this.c.GradMonth);
	}
	
	this.updateClassSectionOptions = function()
	{
	//console.log('updateclassection, c.classyear: ' + this.c.ClassYear + ' l.classYear: ' + this.l.ClassYear);
		if (this.c.ClassYear == this.l.ClassYear) {
			return;
		}
		
		// create filter
		if (this.c.ClassYear == 'all') {
			filter = null;
		} else {
			filter = [];
			for (cs in this.classSections) {
				clSect = this.classSections[cs];
				
				if (clSect.year==this.c.ClassYear) {
					filter.push(cs);
				}
			}
		}
		
	//console.log('update class section options');
	//console.log(this.classSections);
		
		if (typeof(this.l.ClassSection) == 'undefined') {
			this.l.ClassSection = this.c.ClassSection;
		}
				
		var html = buildOptionsList(this.classSections, filter, 'name', this.l.ClassSection, 'all', 'Any Section');
		$(this.sel.ClassSection).html(html);
	}
	
	/**
	 *	Currently just used to populate it once.
	 */
	this.updateClassYearOptions = function()
	{
		if (typeof(this.l.ClassYear) == 'undefined') {
			defaultValue = this.c.ClassYear;
		} else {
			defaultValue = this.l.ClassYear;
		}
	//console.log('updateclassyearoptions: l.classYear: ' + this.l.ClassYear);
		var html = buildOptionsList(this.classSectionYears, null, 'VAL_VAL', defaultValue, 'all', 'All Years');
		$(this.sel.ClassSectionYear).html(html);
	}
	
	this.updateStudentList = function()
	{
		// these track possible values in CURRENTLY display student subset
		this.cVals = {}
		this.cVals.Students = [];
		this.cGradMonth = [];	// NOT DONE
		this.cGradYears = [];	// NOT DONE
		this.cClassYearsTests = [];	// working on..
		this.cClassYears = [];
		this.cClassSections = [];	// NOT DONE
		
		//find students we want: filter by dropdown values when not 'all' is selected
		okcount = 0;
		for (studentId in this.students) {
			s = this.students[studentId];
			var ok = true;
			
			if (this.c.GradYear != 'all') {
				if (s[this.GRADUATION_YEAR] != this.c.GradYear) {
					ok = false;
				}
			}
			
			if (ok && this.c.GradMonth != 'all') {
				if (s[this.GRADUATION_MONTH] != this.c.GradMonth) {
					ok = false;
				}
			}
			
			if (ok && this.c.ClassYear != 'all') {
				// student in class?
				if (typeof(s[this.CLASS_ID]) != 'undefined') {
					var inClassYear = false;
					for (classId in s[this.CLASS_ID]) {
						studentClass = s[this.CLASS_ID][classId];
						studentsClassYear = this.classSections[studentClass]['year'];
						
						//console. log('student ' + studentId + 'is in class:' + classId + ' needed class year:'+this.c.ClassYear+' StudentClassYear:'+studentsClassYear);
						if (this.c.ClassYear == studentsClassYear) {
							inClassYear = true;
						}
					}
					if (!inClassYear) {
						ok = false;
					}
				}
			}
			
			if (ok && this.c.ClassSection != '0' && this.c.ClassSection != 'all') {
				if ($.inArray(this.c.ClassSection, s[this.CLASS_ID]) == -1) {
					ok = false;
				}
			}
			
			// certification levels
			if (ok) {
				//console. log(this.certTypeEmt+' '+this.certTypeAemt+' '+this.certTypePmed+' '+s[this.CERTIFICATION_LEVEL]+' '+this.CERT_EMT+' '+this.CERT_ADV_EMT+' '+this.CERT_PARAMEDIC);
//console.log(s);
				if (!this.certTypeEmt && s[this.CERTIFICATION_LEVEL]==this.CERT_EMT) {
					ok = false;
				}
				
				if (!this.certTypeAemt && s[this.CERTIFICATION_LEVEL]==this.CERT_ADV_EMT) {
					ok = false;
				}
				
				if (!this.certTypePmed && s[this.CERTIFICATION_LEVEL]==this.CERT_PARAMEDIC) {
					ok = false;
				}
			}
			
			if (ok) {
				// record 'current' lists values
				okcount = okcount + 1;
				this.cVals.Students.push(studentId);
				if (typeof(s[this.CLASS_ID]) != 'undefined') {
					for (classId in s[this.CLASS_ID]) {
						thisClassId = s[this.CLASS_ID][classId];
						if (typeof(this.cClassSections[thisClassId]) == 'undefined') { // not recorded class
							this.cClassSections[thisClassId] = true;
							
							// record year too:
							year = this.classSections[thisClassId]['year'];
							if(typeof(this.cClassYearsTests[year]) == 'undefined') {
								this.cClassYearsTests[year] = true;
								this.cClassYears.push(year);
							}
						}
					}
				}
				
			}
		}
		//console.log(okcount);
		
		this.populateStudentListDropdown();
		
		this.updateStudentListField();
		
		this.updateOptions();
		
		// used to track what has changed
		this.l.GradMonth = this.c.GradMonth;
		this.l.GradYear = this.c.GradYear;
		this.l.ClassYear = this.c.ClassYear;
		//console.log('updatestudentlist, setting l.classsection to: ' + this.c.ClassSection);
		this.l.ClassSection = this.c.ClassSection;
	}
	
	this.populateStudentListDropdown = function()
	{
		//console.log('populating student list');
		// This can be populating either a SELECT element or checkboxes
		if ($('*[name="student[student]"]').eq(0).is('select')) {
			var html = "<option value='all' selected='selected'>All ("+this.cVals.Students.length+")</option>";
			
			for (studentId in this.cVals.Students) {
				id = this.cVals.Students[studentId];
				student = this.students[id];
							
				html += "<option value='" + id + "'";
				html += ">" + student[this.FULL_NAME] + "</option>";
			}
			
			$(this.selStudents).html(html);
		} else {
			// checkboxes
			var html = '';
			
			for (studentId in this.cVals.Students) {
				id = this.cVals.Students[studentId];
				student = this.students[id];
							
				html += "<div class='student-filter-results-student'><input value='" + id + "' checked='checked' name='student[student]' class='student_checkbox' type='checkbox' />";
				html += " " + student[this.FULL_NAME] + "</div>";
			}
			$(this.checkboxStudents).html(html);
			
			// bind events to newly created results-checkbox markup
			// if student list is in checkbox mode, then we need to bind an event for checkboxes changing
			$(this.checkboxStudents + ' input[type="checkbox"]').change($.proxy(function(event) {
				this.updateStudentListField();
			}, this));

		}
	}
	
	/**
	 *	Uses: this.cVals.Students to populate hidden student list field
	 *	called from 2 places:
	 *		1. any student selectors change student list
	 *		2. when user selects one student
	 */
	this.updateStudentListField = function()
	{
		//console.log('updating hidden list field');
		// if we have been signalled to AVOID the initial onload override of who is selected, and we have value already in the
		// selected student list, then we KEEP that value, and set available checkboxes accordingly
		var setHiddenValues = true;
		var do_not_onload_flag = $('input[name="picker_do_not_onload"]');
		if (do_not_onload_flag.length == 0) {
			if ($(this.selStudentList).val() != '' && $(do_not_onload_flag).val() == '1') {
				setHiddenValues = false;
				$(do_not_onload_flag).val('0');
				
				// set checkboxes if applicable
				if ($('*[name="student[student]"]').eq(0).is('input[type="checkbox"]')) {
					var defaultSelectedStudents = $(this.selStudentList).val().split(',');
					$('input[name="student[student]"]').each(function(i, elem) {
						if ($.inArray($(elem).val(), defaultSelectedStudents)) {
							$(elem).attr('checked', 'checked');
						} else {
							$(elem).attr('checked', 'false');
						}
					});
				}
			}
		}
		
		if (setHiddenValues) {
			// we pull from either a SELECT element or checkboxes
			if ($('*[name="student[student]"]').eq(0).is('select')) {
	
				// one student or list?
				var studentList = $(this.selStudents).val();
				
				if (studentList=='all') {
					studentList='';
					for (studentId in this.cVals.Students) {
						if (studentList) {
							studentList += ',';
						}
						studentList += this.cVals.Students[studentId];
					}
				}
			} else {
				// pull data from checkboxes
				var studentList = []
				$(this.checkboxStudents + ' input:checked').each(function(i, elem) {
					var studentId = $(elem).val();
					if (typeof(studentId) != 'undefined') {
						if (studentId != '') {
							studentList.push(studentId);
						}
					}
				});
				$('#student-filter-status').html(studentList.length + ' students selected');
				studentList = studentList.join(',');
			}
			
			$(this.selStudentList).val(studentList);
		}
	}
	
	/**
	 *	Not implemented.
	 *	Part of dynamic drop down selections allowing narrowing down selections
	 *	that make difference
	 */
	this.updateOptions = function()
	{
//console.log('updateoptions called');
		this.updateClassSectionOptions();
		//this.updateClassYearOptions();
		
		// dynamic selections: class section years, no time for that
		//this.cClassYears.sort();
		//var previousValue = this.c.ClassYear;
		//$('#student-classyear').html(html);
	}
}

/**
 *	obj		object with all values
 *	filterArr	values to use
 *	column	column to use for caption, or: value=>caption options:
 *		null	id => val
 *		ID_VAL	id => id
 *		VAL_VAL	val => val
 *	selVal	currently selected value
 */
function buildOptionsList(obj, filterArr, column, selVal, addedOptionKey, addedOptionCaption)
{
	var html = '';
	
	if (typeof(addedOptionKey=='string')) {
		html += "<option value='" + addedOptionKey + "'";
		if (selVal == addedOptionKey) {
			html += " selected='selected' ";
		}
		html += ">" + addedOptionCaption + "</option>";
	}

	var idAsCaption = (column=='ID_ID' || column == 'VAL_ID') ? true : false;
	var valueAsCaption = (column=='ID_VAL' || column=='VAL_VAL' || column==null) ? true : false;
	var valueAsId = (column=='VAL_VAL' || column=='VAL_ID') ? true : false;
	
	for (id in obj) {
		row = obj[id];
		
		if (filterArr !== null && $.inArray(id, filterArr) == -1) {
			continue;	// skip current row
		}
		
		val = valueAsId ? row : id;
		html += "<option value='" + val + "'";
		if (selVal == id) {
			html += " selected='selected' ";
		}
		
		if (valueAsCaption) {
			capt = row;
		} else if(idAsCaption ) {
			capt = id;
		} else {
			capt = row[column];
		}
		
		html += ">" + capt + "</option>";
	}
	return html;
}
//var html = '';
//$.each(response, function(id, name) {
//		html += "<option value='" + id + "'";
//		if (lastId == id) {
//			html += " selected='selected' ";
//		}
//		html += ">" + name + "</option>";
//	});
