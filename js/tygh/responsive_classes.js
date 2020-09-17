/**
 * Bind css-classes to body for tracking web-page width
 */

(function(_, $) {

    $.ceEvent('one', 'ce.commoninit', function () {

        var _widths = {
            'screen--xs':       [0, 350],
            'screen--xs-large': [350, 480],
            'screen--sm':       [481, 767],
            'screen--sm-large': [768, 1024],
            'screen--md':       [1024, 1280],
            'screen--md-large': [1280, 1440],
            'screen--lg':       [1440, 1920],
            'screen--uhd':      [1920, 9999]
        }

        var _timeout;
        var _timeoutTime = 200;

        var customClearTimeout = function () {
            clearTimeout(_timeout);
            _timeout = undefined;
        }

        // would work after `_timeoutTime` ms
        var windowResizeHandler = function (event) {
            customClearTimeout();

            var classes = {
                old: '',
                new: ''
            };

            var windowWidth = $(window).width();
            for (className in _widths) {
                if ($('body').hasClass(className)) {
                    classes.old = className;
                    $('body').removeClass(className);
                }

                var width = _widths[className];
                if ((windowWidth >= width[0]) && (windowWidth <= width[1])) {
                    $('body').addClass(className);
                    classes.new = className;
                }
            }

            $.ceEvent('trigger', 'ce.window.resize', [event, classes]);
        }

        // bind onresize event handler to web page
        $(window).on('resize', function (event) {
            if (typeof(_timeout) != typeof(undefined)) {
                customClearTimeout();
            }
            _timeout = setTimeout(windowResizeHandler, _timeoutTime, event);
        });

        // one-time setting class to body
        $(window).trigger('resize');
    });

})(Tygh, Tygh.$);