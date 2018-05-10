function blockUi(block, element, style, msg, settings) {

    // if there are no settings, give it the default blocker
    if (!settings) {
        settings = {
            message:  "<img id='uiBlockerThrobber' src='/images/throbber_small.gif'> Loading...",
			css: { 
                border: 'none',
                padding: '15px',
                backgroundColor: 'background-color: #a29a94',
                '-webkit-border-radius': '10px',
                '-moz-border-radius': '10px',
                'border-radius': '10px',
                opacity: 1,
                color: '#fff',
                'font-family': 'Century Gothic,Arial,Helvetica,sans-serif',
                'font-size': '15pt'
            },
            overlayCSS: {
                backgroundColor: '#fff',
                opacity: .5
            }
        }
    }

    // add the custom message
    if (msg) {
        settings.message = msg;
    }

    // modify specified styles
    if (style == "throbber") {
        settings.message = "<img src='/images/throbber_small.gif'>";
        settings.css.backgroundColor = 'background-color: none';
        settings.css.padding = '0px';
        settings.overlayCSS.height = $(element).height() + 4;
    }

    if (style == "no-msg") {
        settings.message = null;
    }

    // do the blocking or unblocking
	if (block) {
		if (element) {
			$(element).block(settings);
		} else {
			$.blockUI(settings); 
		}
	} else {
		if (element) {
			$(element).unblock();
		} else {
			$.unblockUI();			
		}
	}
}

function positionBlocker(element) {
    // grab the blocker overlay
    var overlay = $(element).find(".blockOverlay");
    // grab the blocker message
    var message = $(element).find(".blockMsg");

    // get info about the thing you want to block
    var elementHeight = $(element).height();
    var elementWidth = $(element).width();
    var elementTop = $(element).offset().top;
    var elementLeft = $(element).offset().left;

    // do some math!
    // set the dimensions and positions of the blocker elements
    $(overlay).css({
        "height": elementHeight + 2,
        "width": elementWidth + 4,
        "top": elementTop - $(overlay).offset().top - 1,
        "left": elementLeft - $(overlay).offset().left - 1
    });
    $(message).css({
        "width": elementWidth/3,
        "height": elementHeight/3,
        "left": elementLeft - $(message).offset().left + elementWidth/3 - 1
    });

}

function resizeOverlay(element) {
    // grab the blocker overlay
    var overlay = $(element).find(".blockOverlay");

    // get info about the thing you want to block
    var elementHeight = $(element).height();
    var elementWidth = $(element).width();

    // set the dimensions of the blocker
    $(overlay).css({
        "height": elementHeight + 2,
        "width": elementWidth + 4
    });

}


