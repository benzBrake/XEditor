// jQuery resizeEnd Event v1.0.1
// Copyright (c) 2013 Giuseppe Gurgone

// This work is licensed for reuse under the MIT license.
// See the license file for details https://github.com/giuseppeg/jQuery-resizeEnd/blob/master/LICENSE

// About:
// jQuery resizeEnd defines a special event
// that is fired when the JavaScript "resize" event has finished.
//
// It also defines an alias function named after the event name
// which binds an event handler to the "resizeEnd" event,
// or trigger that event on an element.

// Usage:
//
// $(window).on("resizeEnd", function (event) {
//      // go nuts
// });
//
// or use its alias function:
//
// $(window).resizeEnd(function (event) {
//      // go nuts
// });

// Project Home - http://giuseppeg.github.io/jQuery-resizeEnd/
// GitHub repo  - http://github.com/giuseppeg/jQuery-resizeEnd/


(function ($, window) {
    var jqre = {};

    // Settings
    // eventName: the special event name.
    jqre.eventName = "resizeEnd";

    // Settings
    // delay: The numeric interval (in milliseconds)
    // at which the resizeEnd event polling loop executes.
    jqre.delay = 250;

    // Poll function
    // triggers the special event jqre.eventName when resize ends.
    // Executed every jqre.delay milliseconds while resizing.
    jqre.poll = function () {
        var elem = $(this),
            data = elem.data(jqre.eventName);

        // Clear the timer if we are still resizing
        // so that the delayed function is not exectued
        if (data.timeoutId) {
            window.clearTimeout(data.timeoutId);
        }

        // triggers the special event
        // after jqre.delay milliseconds of delay
        data.timeoutId = window.setTimeout(function () {
            elem.trigger(jqre.eventName);
        }, jqre.delay);
    };

    // Special Event definition
    $.event.special[ jqre.eventName ] = {

        // setup:
        // Called when an event handler function
        // for jqre.eventName is attached to an element
        setup: function () {
            var elem = $(this);
            elem.data(jqre.eventName, {});

            elem.on("resize", jqre.poll);
        },

        // teardown:
        // Called when the event handler function is unbound
        teardown: function () {
            var elem = $(this),
                data = elem.data(jqre.eventName);

            if (data.timeoutId) {
                window.clearTimeout(data.timeoutId);
            }

            elem.removeData(jqre.eventName);
            elem.off("resize", jqre.poll);
        }
    };

    // Creates an alias function
    $.fn[ jqre.eventName ] = function (data, fn) {
        return arguments.length > 0 ?
            this.on(jqre.eventName, null, data, fn) :
            this.trigger(jqre.eventName);
    };

}(jQuery, this));