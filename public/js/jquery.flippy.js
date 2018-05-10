/*

FLIPPY jQuery plugin (http://guilhemmarty.com/flippy)

@author : Guilhem MARTY (bonjour@guilhemmarty.com)

@version: 1.0.3

@changelog:
Mar 30 2013 - v1.0.3 : bug fix on IE8/IE9 with explorerCanvas + add multiple simultaneous flippy animations

Mar 17 2013 - v1.0.2 : bug fix with IE10+. Can use rgba in color and color target

Feb 11 2012 - v1.0.1 : bug fix with IE9

Feb 11 2012 - v1.0 : First release

*/

(function($){
	var _ColorsRef = {
		'aliceblue':'#f0f8ff',
		'antiquewhite':'#faebd7',
		'aqua':'#00ffff',
		'aquamarine':'#7fffd4',
		'azure':'#f0ffff',
		'beige':'#f5f5dc',
		'bisque':'#ffe4c4',
		'black':'#000000',
		'blanchedalmond':'#ffebcd',
		'blue':'#0000ff',
		'blueviolet':'#8a2be2',
		'brown':'#a52a2a',
		'burlywood':'#deb887',
		'cadetblue':'#5f9ea0',
		'chartreuse':'#7fff00',
		'chocolate':'#d2691e',
		'coral':'#ff7f50',
		'cornflowerblue':'#6495ed',
		'cornsilk':'#fff8dc',
		'crimson':'#dc143c',
		'cyan':'#00ffff',
		'darkblue':'#00008b',
		'darkcyan':'#008b8b',
		'darkgoldenrod':'#b8860b',
		'darkgray':'#a9a9a9',
		'darkgrey':'#a9a9a9',
		'darkgreen':'#006400',
		'darkkhaki':'#bdb76b',
		'darkmagenta':'#8b008b',
		'darkolivegreen':'#556b2f',
		'darkorange':'#ff8c00',
		'darkorchid':'#9932cc',
		'darkred':'#8b0000',
		'darksalmon':'#e9967a',
		'darkseagreen':'#8fbc8f',
		'darkslateblue':'#483d8b',
		'darkslategray':'#2f4f4f',
		'darkslategrey':'#2f4f4f',
		'darkturquoise':'#00ced1',
		'darkviolet':'#9400d3',
		'deeppink':'#ff1493',
		'deepskyblue':'#00bfff',
		'dimgray':'#696969',
		'dimgrey':'#696969',
		'dodgerblue':'#1e90ff',
		'firebrick':'#b22222',
		'floralwhite':'#fffaf0',
		'forestgreen':'#228b22',
		'fuchsia':'#ff00ff',
		'gainsboro':'#dcdcdc',
		'ghostwhite':'#f8f8ff',
		'gold':'#ffd700',
		'goldenrod':'#daa520',
		'gray':'#808080',
		'grey':'#808080',
		'green':'#008000',
		'greenyellow':'#adff2f',
		'honeydew':'#f0fff0',
		'hotpink':'#ff69b4',
		'indianred ':'#cd5c5c',
		'indigo  ':'#4b0082',
		'ivory':'#fffff0',
		'khaki':'#f0e68c',
		'lavender':'#e6e6fa',
		'lavenderblush':'#fff0f5',
		'lawngreen':'#7cfc00',
		'lemonchiffon':'#fffacd',
		'lightblue':'#add8e6',
		'lightcoral':'#f08080',
		'lightcyan':'#e0ffff',
		'lightgoldenrodyellow':'#fafad2',
		'lightgray':'#d3d3d3',
		'lightgrey':'#d3d3d3',
		'lightgreen':'#90ee90',
		'lightpink':'#ffb6c1',
		'lightsalmon':'#ffa07a',
		'lightseagreen':'#20b2aa',
		'lightskyblue':'#87cefa',
		'lightslategray':'#778899',
		'lightslategrey':'#778899',
		'lightsteelblue':'#b0c4de',
		'lightyellow':'#ffffe0',
		'lime':'#00ff00',
		'limegreen':'#32cd32',
		'linen':'#faf0e6',
		'magenta':'#ff00ff',
		'maroon':'#800000',
		'mediumaquamarine':'#66cdaa',
		'mediumblue':'#0000cd',
		'mediumorchid':'#ba55d3',
		'mediumpurple':'#9370d8',
		'mediumseagreen':'#3cb371',
		'mediumslateblue':'#7b68ee',
		'mediumspringgreen':'#00fa9a',
		'mediumturquoise':'#48d1cc',
		'mediumvioletred':'#c71585',
		'midnightblue':'#191970',
		'mintcream':'#f5fffa',
		'mistyrose':'#ffe4e1',
		'moccasin':'#ffe4b5',
		'navajowhite':'#ffdead',
		'navy':'#000080',
		'oldlace':'#fdf5e6',
		'olive':'#808000',
		'olivedrab':'#6b8e23',
		'orange':'#ffa500',
		'orangered':'#ff4500',
		'orchid':'#da70d6',
		'palegoldenrod':'#eee8aa',
		'palegreen':'#98fb98',
		'paleturquoise':'#afeeee',
		'palevioletred':'#d87093',
		'papayawhip':'#ffefd5',
		'peachpuff':'#ffdab9',
		'peru':'#cd853f',
		'pink':'#ffc0cb',
		'plum':'#dda0dd',
		'powderblue':'#b0e0e6',
		'purple':'#800080',
		'red':'#ff0000',
		'rosybrown':'#bc8f8f',
		'royalblue':'#4169e1',
		'saddlebrown':'#8b4513',
		'salmon':'#fa8072',
		'sandybrown':'#f4a460',
		'seagreen':'#2e8b57',
		'seashell':'#fff5ee',
		'sienna':'#a0522d',
		'silver':'#c0c0c0',
		'skyblue':'#87ceeb',
		'slateblue':'#6a5acd',
		'slategray':'#708090',
		'slategrey':'#708090',
		'snow':'#fffafa',
		'springgreen':'#00ff7f',
		'steelblue':'#4682b4',
		'tan':'#d2b48c',
		'teal':'#008080',
		'thistle':'#d8bfd8',
		'tomato':'#ff6347',
		'turquoise':'#40e0d0',
		'violet':'#ee82ee',
		'wheat':'#f5deb3',
		'white':'#ffffff',
		'whitesmoke':'#f5f5f5',
		'yellow':'#ffff00',
		'yellowgreen':'#9acd32'
	};
	
	$.fn.flippy = function(opts){
		
		opts = $.extend({
			step_ang:10,
			refresh_rate:15,
			duration:300,
			depth:0.12,
			color_target:"white",
			light:60,
			content:"",
			direction:"LEFT",
			onStart:function(){},
			onMidway:function(){},
			onFinish:function(){}
		}, opts);
		_isIE = (navigator.appName == "Microsoft Internet Explorer");
		_Support_Canvas = window.HTMLCanvasElement;
		_Container = null;
		var _i = 1;
		return this.each(function(){
    		_Container = $this = $(this);
			if(!$this.hasClass("flippy_active")){
    			_Color_target_is_rgba = (opts.color_target.substr(0,5) == "rgba(");
    			_Color = $this.css("background-color");
    			var _FP = {
        			"_Ang"                  : 0,
        			"_Step_ang"             : (opts.refresh_rate/opts.duration)*200,
        			"_Refresh_rate"         : opts.refresh_rate,
        			"_Depth"                : opts.depth,
        			"_CenterX"              : '',
        			"_CenterY"              : '',
        			"_Color"                : convertColor(_Color),
        			"_Color_target_is_rgba" : _Color_target_is_rgba,
        			"_Color_target_alpha"   : (_Color_target_is_rgba)? eval(opts.color_target.substr(3,opts.color_target.length-4).split(',')[3]) : 1,
        			"_Color_alpha"          : (_Color.substr(0,5) == "rgba(")? eval(_Color.substr(3,_Color.length-4).split(',')[3]) : 1,
        			"_Color_target"         : convertColor(opts.color_target),
        			"_Direction"            : opts.direction,
        			"_Light"                : opts.light,
        			"_Content"              : (typeof opts.content == "object") ? opts.content.html() : opts.content,
        			"_Before"               : opts.onStart,
        			"_Midway"               : opts.onMidway,
        			"_After"                : opts.onFinish,
        			"_nW"                   : $this.width(),
        			"_nH"                   : $this.height(),
        			"_W"                    : $this.outerWidth(),
        			"_H"                    : $this.outerHeight()
    			};
    			_UID = Math.floor(Math.random()* 1000000);
    			_FP._Before();
    			$this
    			    .data('_UID',_UID)
    			    .addClass('flippy_active')
    				.empty()
    				.css({
    					 "opacity":_FP._Color_alpha,
    					 "background":"none",
    					 "position":"relative",
    					 "overflow":"visible"
    				});
    				
    				switch(_FP._Direction){
    					case "TOP":
    						_FP._CenterX = (Math.sin(Math.PI/2)*_FP._nW*_FP._Depth);
    						_FP._CenterY = _FP._H/2;
    						var cv_pattern = '<canvas id="flippy'+_UID+'" class="flippy" width="'+(_FP._W+(2*_FP._CenterX))+'" height="'+_FP._H+'"></canvas>';
    						new_flippy(cv_pattern,_UID);
    						$this.find("#flippy"+_UID)
    							.css({
    								 "position":"absolute",
    								 "top":"0",
    								 "left":"-"+_FP._CenterX+"px"
    							});
    					break;
    					case "BOTTOM":
    						_FP._CenterX = (Math.sin(Math.PI/2)*_FP._nW*_FP._Depth);
    						_FP._CenterY = _FP._H/2;
    						var cv_pattern = '<canvas id="flippy'+_UID+'" class="flippy" width="'+(_FP._W+(2*_FP._CenterX))+'" height="'+_FP._H+'"></canvas>';
    						new_flippy(cv_pattern,_UID);
    						$this.find("#flippy"+_UID)
    							.css({
    								 "position":"absolute",
    								 "top":"0",
    								 "left":"-"+_FP._CenterX+"px"
    							});
    					break;
    					case "LEFT":
    						_FP._CenterY = (Math.sin(Math.PI/2)*_FP._nH*_FP._Depth);
    						_FP._CenterX = _FP._W/2;
    						var cv_pattern = '<canvas id="flippy'+_UID+'" class="flippy" width="'+_FP._W+'" height="'+(_FP._H+(2*_FP._CenterY))+'"></canvas>';
    						new_flippy(cv_pattern,_UID);
    						$this.find("#flippy"+_UID)
    							.css({
    								 "position":"absolute",
    								 "top":"-"+_FP._CenterY+"px",
    								 "left":"0"
    							});
    					break;
    					case "RIGHT":
    						_FP._CenterY = (Math.sin(Math.PI/2)*_FP._nH*_FP._Depth);
    						_FP._CenterX = _FP._W/2;
    						var cv_pattern = '<canvas id="flippy'+_UID+'" class="flippy" width="'+_FP._W+'" height="'+(_FP._H+(2*_FP._CenterY))+'"></canvas>';
    						new_flippy(cv_pattern,_UID);
    						$this.find("#flippy"+_UID)
    							.css({
    								 "position":"absolute",
    								 "top":"-"+_FP._CenterY+"px",
    								 "left":"0"
    							});
    					break;
    				}
    			$this.data("flippy_FP",_FP);
    			drawFlippy($(this));
            }
		});
		
	}
	
	function new_flippy(cv_pattern,_UID){
		if(_isIE && !_Support_Canvas){
    		
    		$this
    		  .addClass("flippy_container")
    		  .attr("id","flippy_container"+_UID);
			var $that = document.getElementById("flippy_container"+_UID);
			var cv = document.createElement(cv_pattern);
			
			$that.appendChild(cv);
		}else{
			$this.append(cv_pattern);
		}
	}
	
	function drawFlippy($t){
    	var _FP = $t.data("flippy_FP");
		_FP._Ang += _FP._Step_ang;
		_UID = $t.data("_UID");
		if(_FP._Ang > 90 && _FP._Ang <= (90+_FP._Step_ang)){
			_FP._Midway();
			$t.css({"opacity":_FP._Color_target_alpha});
		}
		_FP._Ang = (_FP._Ang > (180+_FP._Step_ang)) ? _FP._Ang-(180+_FP._Step_ang) : _FP._Ang;
		var PI = Math.PI
		var rad = (_FP._Ang/180)*PI;
		
		var canvas = document.getElementById("flippy"+_UID);
		if(canvas == null){ return; }
		if(_isIE && !_Support_Canvas){ G_vmlCanvasManager.initElement(canvas);}
		var ctx = canvas.getContext("2d");
		ctx.clearRect(0, 0, _FP._W+(2*_FP._CenterX), _FP._H+(2*_FP._CenterY));
		ctx.beginPath();
		var deltaH = Math.sin(rad)*_FP._H*_FP._Depth;
		var deltaW = Math.sin(rad)*_FP._W*_FP._Depth;
		
		switch(_FP._Direction){
			case "LEFT" :
				var X = Math.cos(rad)*(_FP._W/2);
				ctx.fillStyle = (_FP._Ang > 90) ? changeColor(_FP._Color_target,Math.floor(Math.sin(rad)*_FP._Light)) : changeColor(_FP._Color,-Math.floor(Math.sin(rad)*_FP._Light));
				ctx.moveTo(_FP._CenterX-X,_FP._CenterY+deltaH);//TL
				ctx.lineTo(_FP._CenterX+X,_FP._CenterY-deltaH);//TR
				ctx.lineTo(_FP._CenterX+X,_FP._CenterY+_FP._H+deltaH);//BR
				ctx.lineTo(_FP._CenterX-X,_FP._CenterY+_FP._H-deltaH);//BL
				ctx.lineTo(_FP._CenterX-X,_FP._CenterY);//loop
				ctx.fill();
			break;
			case "RIGHT" :
				var X = Math.cos(rad)*(_FP._W/2);
				ctx.fillStyle = (_FP._Ang > 90) ? changeColor(_FP._Color_target,-Math.floor(Math.sin(rad)*_FP._Light)) : changeColor(_FP._Color,Math.floor(Math.sin(rad)*_FP._Light));
				ctx.moveTo(_FP._CenterX+X,_FP._CenterY+deltaH);//TL
				ctx.lineTo(_FP._CenterX-X,_FP._CenterY-deltaH);//TR
				ctx.lineTo(_FP._CenterX-X,_FP._CenterY+_FP._H+deltaH);//BR
				ctx.lineTo(_FP._CenterX+X,_FP._CenterY+_FP._H-deltaH);//BL
				ctx.lineTo(_FP._CenterX+X,_FP._CenterY);//loop
				ctx.fill();
			break;
			case "TOP" :
				var Y = Math.cos(rad)*(_FP._H/2);
				ctx.fillStyle = (_FP._Ang > 90) ? changeColor(_FP._Color_target,-Math.floor(Math.sin(rad)*_FP._Light)) : changeColor(_FP._Color,Math.floor(Math.sin(rad)*_FP._Light));
				ctx.moveTo(_FP._CenterX+deltaW,_FP._CenterY-Y);//TL
				ctx.lineTo(_FP._CenterX-deltaW,_FP._CenterY+Y);//TR
				ctx.lineTo(_FP._CenterX+_FP._W+deltaW,_FP._CenterY+Y);//BR
				ctx.lineTo(_FP._CenterX+_FP._W-deltaW,_FP._CenterY-Y);//BL
				ctx.lineTo(_FP._CenterX,_FP._CenterY-Y);//loop
				ctx.fill();
			break;
			case "BOTTOM" :
				var Y = Math.cos(rad)*(_FP._H/2);
				ctx.fillStyle = (_FP._Ang > 90) ? changeColor(_FP._Color_target,Math.floor(Math.sin(rad)*_FP._Light)) : changeColor(_FP._Color,-Math.floor(Math.sin(rad)*_FP._Light));
				ctx.moveTo(_FP._CenterX+deltaW,_FP._CenterY+Y);//TL
				ctx.lineTo(_FP._CenterX-deltaW,_FP._CenterY-Y);//TR
				ctx.lineTo(_FP._CenterX+_FP._W+deltaW,_FP._CenterY-Y);//BR
				ctx.lineTo(_FP._CenterX+_FP._W-deltaW,_FP._CenterY+Y);//BL
				ctx.lineTo(_FP._CenterX,_FP._CenterY+Y);//loop
				ctx.fill();
			break;
		}
		
		if(_FP._Ang < 180){
			setTimeout(function(){ drawFlippy($t) },_FP._Refresh_rate);
		}else{
			$t
				.removeClass("flippy_active")
				.css({
					 "background":_FP._Color_target
				})
				.append(_FP._Content)
				.removeClass("flippy_container")
				.find(".flippy")
					.remove();
				
			_FP._After();
		}
		$t.data("flippy_FP",_FP);
	}
	
	function convertColor(thecolor){
		try{
			thecolor = (eval('_ColorsRef.'+thecolor) != null)? eval('_ColorsRef.'+thecolor) : thecolor;
		}catch(err){
		
		}
	
		if(thecolor.substr(0,4) == "rgb("){
			thecolor = "#"
				+toHex(eval(thecolor.substr(4,thecolor.length).split(',')[0]))
				+toHex(eval(thecolor.substr(3,thecolor.length).split(',')[1]))
				+toHex(eval(thecolor.substr(3,thecolor.length-4).split(',')[2]))
		}

		if(thecolor.substr(0,5) == "rgba("){
			thecolor = "#"
				+toHex(eval(thecolor.substr(5,thecolor.length).split(',')[0]))
				+toHex(eval(thecolor.substr(3,thecolor.length).split(',')[1]))
				+toHex(eval(thecolor.substr(3,thecolor.length-4).split(',')[2]))
		}

		return thecolor;
	}
	
	function toDec(hex){
		dec = parseInt(hex,16);
		return dec;
	}
	
	function toHex(dec){
		var modulos = new Array();
		while(Math.floor(dec)>16){
			modulos.push(dec%16);
			dec = Math.floor(dec/16);
		}
		
		var Hex;
		switch(dec){
			case 10 : Hex = "A"; break;
			case 11 : Hex = "B"; break;
			case 12 : Hex = "C"; break;
			case 13 : Hex = "D"; break;
			case 14 : Hex = "E"; break;
			case 15 : Hex = "F"; break;
			default : Hex = ""+dec; break;
		}
		for(i=modulos.length-1;i>=0;i--){
			switch(modulos[i]){
				case 10 : Hex += "A"; break;
				case 11 : Hex += "B"; break;
				case 12 : Hex += "C"; break;
				case 13 : Hex += "D"; break;
				case 14 : Hex += "E"; break;
				case 15 : Hex += "F"; break;
				default : Hex += ""+modulos[i]; break;
			}
		}
		if(Hex.length == 1 ){
			return "0"+Hex;
		}else{
			return Hex;
		}
	}
	
	function changeColor(colorHex,step){
		var redHex = colorHex.substr(1,2);
		var greenHex = colorHex.substr(3,2);
		var blueHex = colorHex.substr(5,2);
		
		var redDec = (toDec(redHex)+step > 255) ? 255 : toDec(redHex)+step;
		var greenDec = (toDec(greenHex)+step > 255) ? 255 : toDec(greenHex)+step;
		var blueDec = (toDec(blueHex)+step > 255) ? 255 : toDec(blueHex)+step;
		
		redHex = (redDec <= 0) ? "00" : toHex(redDec);
		greenHex = (greenDec <= 0) ? "00" : toHex(greenDec);
		blueHex = (blueDec <= 0) ? "00" : toHex(blueDec);
		
		return "#"+redHex+greenHex+blueHex;
	}

})(jQuery)

// special stuff just for the way we use flippys
function initFlippyDivs() {
	initFlippyStyles();
	
	$(".can-cannot").click(function(e){
		flipDiv($(this));
		togglePermissions($(this));
	});
		
	$(".with-without").click(function(e){
		flipDiv($(this));
	});
	
	$(".does-doesnot").click(function(e){
		flipDiv($(this));
	});
	
	$(".regardlessof-forspecific").click(function(e){
		flipDiv($(this));
	});
	
	$(".assign-donotassign").click(function(e){
		flipDiv($(this));
	});
	
	$(".all-specific").click(function(e){
		flipDiv($(this));
	});
	
	$(".students-instructors").click(function(e){
		flipDiv($(this));
	});

    $(".active-inactive-flip").click(function(e){
        flipDiv($(this));
    });
	
	$(".lbs-kgs").click(function(e){
		flipDiv($(this));
	});
}

	
// set up styles/buttons/modals
function initFlippyStyles(){
		
	$(".with-without").each(function(){
		var elementId = $(this).attr('id').substring(5);
		if ($('#'+elementId).val() == 1) {
			$(this).html('with');
			$(this).addClass('with');
		} else {
			$(this).html('without');
			$(this).addClass('without');
		}
	});
	
	$(".does-doesnot").each(function(){
		if ($(this).hasClass("doesnot")) {
			$(this).html('does');
			$(this).addClass('does');
			$(this).removeClass('doesnot');
		} else {
			$(this).html('does not');
			$(this).removeClass('does');
			$(this).addClass('doesnot');
		}
	});
	
	$(".reglardlessof-forspecific").each(function(){
		if ($(this).hasClass("forspecific")) {
			$(this).html('reglardless of');
			$(this).addClass('reglardlessof');
			$(this).removeClass('forspecific');
		} else {
			$(this).html('for specific');
			$(this).removeClass('reglardlessof');
			$(this).addClass('forspecific');
		}
	});
		
		
	$(".assign-donotassign").each(function(){
		if ($(this).hasClass("donotassign")) {
			$(this).html('Do not assign');
			$(this).removeClass('assign');
			$(this).addClass('donotassign');
		} else {
			$(this).html('Assign');
			$(this).addClass('assign');
			$(this).removeClass('donotassign');
		}
	});
	
	$(".all-specific").each(function(){
		if ($(this).hasClass("specific")) {
			$(this).html('specific');
			$(this).removeClass('all');
			$(this).addClass('specific');
		} else {
			$(this).html('all');
			$(this).addClass('all');
			$(this).removeClass('specific');

		}
	});
	
	$(".can-cannot").each(function(){
		var elementId = $(this).attr('id').substring(4);
		if ($('#'+elementId).val() == 1) {
			$(this).html('can');
			$(this).addClass('can');
		} else {
			$(this).html('cannot');
			$(this).addClass('cannot');
			togglePermissions($(this));
		}
	});

    $(".active-inactive-flip").each(function(){
        var elementId = $(this).attr('id').substring(4);
        if ($('#'+elementId).val() == 1) {
            $(this).html('active');
            $(this).addClass('active-flip');
        } else {
            $(this).html('inactive');
            $(this).addClass('inactive-flip');
        }
    });
	
	$(".students-instructors").each(function(){
		var el_id = $(this).attr("id").split("_");
		el_id = el_id[0];
		
		if ($('#'+el_id).val() == 1) {
			$(this).html('students');
			$(this).addClass('students');
		} else {
			$(this).html('instructors');
			$(this).addClass('instructors');
			togglePermissions($(this));
		}
	});
	
	$(".lbs-kgs").each(function(){
		var el_id = $(this).attr("id").split("-");
		el_id = el_id[0];
		
		if ($('#'+el_id).val() == 1) {
			$(this).html('lbs');
			//$(this).addClass('students');
		} else {
			$(this).html('kgs');
			//$(this).addClass('instructors');
		}
	});
		
	// enter key
	$(document).keypress(function(e) {
		if(e.which == 13) {
			e.preventDefault();
			$("input").each(function(){
				if($(this).hasClass("fancy-input-focus")){
					$(this).blur();
				}
			});
		}
	});
	
}
	
function flipDiv(div){
	if ($(div).hasClass('no-flip')) {
		return;
	}
	var oldContent = div.html();
	var content;
	var direction;
	var color_target;
	var newValue;
	
	if (oldContent == 'can') {
		content = 'cannot';
		direction = 'TOP';
		color_target = 'gray';
			
		// set the new value
		$('#'+$(div).attr('id').substring(4)).val(0).trigger("change");
	}
	if (oldContent == 'students') {
		content = 'instructors';
		direction = 'TOP';
		color_target = 'gray';
			
		// set the new value
		var el_id = $(div).attr("id").split("_");
		el_id = el_id[0];
		
		// set the new value
		$('#'+el_id).val(0).trigger("change");
	}
	if (oldContent == 'with') {
		content = 'without';
		direction = 'TOP';
		color_target = 'lightgray';
		
		// set the new value
		$('#'+$(div).attr('id').substring(5)).val(0).trigger("change");
	}
	if (oldContent == 'does') {
		content = 'does not';
		direction = 'TOP';
		color_target = 'lightgray';
		
		var el_id = $(div).attr("id").split("_");
		el_id = el_id[0];
		
		// set the new value
		$('#'+el_id).val(0).trigger("change");
	}
	if (oldContent == 'regardless of') {
		content = 'for specific';
		direction = 'TOP';
		color_target = 'lightgray';
		
		var el_id = $(div).attr("id").split("_");
		el_id = el_id[0];
		
		// set the new value
		$('#'+el_id).val(0).trigger("change");
	}
	if (oldContent == 'Assign') {
		content = 'Do not assign';
		direction = 'TOP';
		color_target = 'lightgray';
		
		var el_id = $(div).attr("id").split("_");
		el_id = el_id[0];
		
		// set the new value
		$('#'+el_id).val(0).trigger("change");
	}
	if (oldContent == 'all') {
		content = 'specific';
		direction = 'TOP';
		color_target = 'lightgray';
		
		var el_id = $(div).attr("id").split("_");
		el_id = el_id[0];
		
		// set the new value
		$('#'+el_id).val(0).trigger("change");
	}
	if (oldContent == 'cannot') {
		content = 'can';
		direction = 'BOTTOM';
		color_target = 'lightgray';
			
		// set the new value
		$('#'+$(div).attr('id').substring(4)).val(1).trigger("change");
	}
	if (oldContent == 'without') {
		content = 'with';
		direction = 'BOTTOM';
		color_target = 'gray';
			
		// set the new value
		$('#'+$(div).attr('id').substring(5)).val(1).trigger("change");
	}
	if (oldContent == 'does not') {
		content = 'does';
		direction = 'BOTTOM';
		color_target = 'gray';
			
		var el_id = $(div).attr("id").split("_");
		el_id = el_id[0];
		
		// set the new value
		$('#'+el_id).val(1).trigger("change");
	}
	if (oldContent == 'instructors') {
		content = 'students';
		direction = 'BOTTOM';
		color_target = 'gray';
			
		var el_id = $(div).attr("id").split("_");
		el_id = el_id[0];
		
		// set the new value
		$('#'+el_id).val(1).trigger("change");
	}
	if (oldContent == 'for specific') {
		content = 'regardless of';
		direction = 'BOTTOM';
		color_target = 'gray';
			
		var el_id = $(div).attr("id").split("_");
		el_id = el_id[0];
		
		// set the new value
		$('#'+el_id).val(1).trigger("change");
	}
	if (oldContent == 'Do not assign') {
		content = 'Assign';
		direction = 'BOTTOM';
		color_target = 'gray';
			
		var el_id = $(div).attr("id").split("_");
		el_id = el_id[0];
		
		// set the new value
		$('#'+el_id).val(1).trigger("change");
	}
	if (oldContent == 'specific') {
		content = 'all';
		direction = 'BOTTOM';
		color_target = 'gray';
			
		var el_id = $(div).attr("id").split("_");
		el_id = el_id[0];
		
		// set the new value
		$('#'+el_id).val(1).trigger("change");
	}

    if (oldContent == 'active') {
        content = 'inactive';
        direction = 'TOP';
        color_target = 'gray';

        // set the new value
        $('#'+$(div).attr('id').substring(4)).val(0).trigger("change");
    }
    if (oldContent == 'inactive') {
        content = 'active';
        direction = 'BOTTOM';
        color_target = 'lightgray';

        // set the new value
        $('#'+$(div).attr('id').substring(4)).val(1).trigger("change");
    }

	// give the parent div content to maintain
	// do the flip
	div.flippy({
		color_target: color_target,
		content: content,
		direction: direction,
		duration:"300",
		onStart:function(){
            //stape: Stupid hack, please don't judge (I can't name the flippy active because there's already a class called active, this code needs to be refactored
            if (oldContent == 'active') {
                var old_class_name = 'active-flip'
            } else if (oldContent = 'inactive') {
                var old_class_name = 'inactive-flip'
            } else {
                var old_class_name = oldContent.replace(/\s+/g, '').toLowerCase();
            }
			$(div).removeClass(old_class_name);
		},
		onFinish:function(){
			// remove spaces from the content variable
            //stape: Stupid hack, please don't judge, this code needs to be refactored
            if (content == 'active') {
                var class_name = 'active-flip'
            } else if (content = 'inactive') {
                var class_name = 'inactive-flip'
            } else {
                var class_name = content.replace(/\s+/g, '').toLowerCase();
            }
			$(div).addClass(class_name);
			$(div).css({'background' : ''}); // remove the background added by flippy
		}
	});
}
	
function togglePermissions(canDiv){
	var elementId = $(canDiv).attr('id').substring(4);
	var value = $('#'+elementId).val();

	var permissionDiv = $(canDiv).parent().parent().find('.permission_phrase');
	var periodSpan = $(canDiv).parent().parent().find('.period');
	
	if (value == 1) {
		$(permissionDiv).css({'opacity' : '', 'cursor' : ''});
		$('#with_'+elementId+'_permission').removeClass('no-flip');
		// add the period in the right place
		$(permissionDiv).find('.floater').html('permission.');
		$(periodSpan).html('');
	} else {
		$(permissionDiv).css({'opacity' : '0.35', 'cursor' : 'default'});
		$('#with_'+elementId+'_permission').addClass('no-flip');
		$(permissionDiv).find('.floater').html('permission');
		$(periodSpan).html('.');
	}
}