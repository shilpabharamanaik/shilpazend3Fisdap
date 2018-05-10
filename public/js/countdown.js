(function() {
  'use strict';

  var root = this;

  var Countdown = function(duration, onTick, onComplete) {
    var secondsLeft = Math.round(duration)
        , tick = function() {
          if (secondsLeft > 0) {
              onTick(secondsLeft);
              secondsLeft -= 1;
          } else {
              clearInterval(interval);
              onComplete();
          }
        }
        // Setting the interval, by call tick and passing through this via a self-calling function wrap.
        , interval = setInterval(
          (function(self){
            return function() { tick.call(self); };
          })(this), 1000
        );

    // First tick.
    tick.call(this);

    return {
      abort: function() {
        clearInterval(interval);
      }

      , getRemainingTime: function() {
        return secondsLeft;
      }
    };
  };

  root.Countdown = Countdown;

}).call(this);

/**
 * A function to allow an action to be delayed via a countdown
 * @param element the dom element container where the countdown should happen
 * @param action the function of what should happen after the delay
 * @param countdownId an id for the countdown container; used for specialized styling
 * @param time the delay time, in seconds; defaults to 5 seconds
 * @param actionDesc a description of the action for use in the countdown message; defaults to "Deleting"
 * @returns {boolean}
 */
function delayedAction(element, action, countdownId, time, actionDesc) {
    // sanity check
    if (!element) { return false; }
    if (!action) { return false; }

    // set defaults
    if (!countdownId) { countdownId = "countdown"; }
    if (!time) { time = 5; }
    if (!actionDesc) { actionDesc = "Deleting"; }

    // hide the current contents of the element
    $(element).children().css({"opacity": "0"});

    // make sure the element is relative, then add the countdown container
    var container = "<div id='"+countdownId+"' class='countdown'><a class='cancelTrigger'>Cancel</a><div class='countdownMsg'></div></div>";
    $(element).css({"position": "relative"}).append(container);
    var countdownContainer = $("#"+countdownId+".countdown");
    var countdownMsg = $("#"+countdownId+" .countdownMsg");
    var cancelTrigger = $("#"+countdownId+" .cancelTrigger");


    var submitCountdown = new Countdown(time, function (seconds) {
        countdownMsg.text(actionDesc + " in " + seconds + '...');
    }, function () {
        $(countdownContainer).remove();
        $(element).children().css({"opacity": "1"});
        action();
    });

    cancelTrigger.on('click', function () {
        submitCountdown.abort();
        $(countdownContainer).remove();
        $(element).children().css({"opacity": "1"});
    });
}
