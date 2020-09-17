(function ($) {

    $.fn.ceLongtap = function (args) {

        var plugin = function () {

            var pluginInstanceStorage = {
                selected: 0,
                quickMode: false,
                allowQuickMode: args.allowQuickMode || true,
                elements: []
            }

            var preventClick = args.preventClick || false;
            var preventSelect = args.preventSelect || true;
            var preventContext = args.preventContext || true;

            var eachFunction = function (index, self) {
                var $self = $(self);
                $self.storage = pluginInstanceStorage;

                var timer = undefined;
                var startTimer = undefined;

                var isSelected = false;
                var isPreStart = false;

                var isTapStart = false;
                var isTapStartStamp = 0;

                var shouldIPreventClick = false;

                var clearingTimers = function (variable, clearCallback, event, stopFlag) {
                    if (!!variable) {
                        clearCallback();

                        if (args.onStop) {
                            if (stopFlag) {
                                args.onStop(event, $self);
                            }
                        }
                    }
                };

                var timeouts = {
                    mainDelay: args.timeout || 1000,
                    mainDelayClear: function () {
                        clearTimeout(timer);
                        timer = undefined;
                    },

                    onStartDelay: args.onStartDelay || 10,
                    onStartDelayClear: function () {
                        clearTimeout(startTimer);
                        startTimer = undefined;
                    },
                };

                var handlersSuccess = {
                    select: function (event) {
                        pluginInstanceStorage.selected++;

                        if (args.onSuccess) {
                            args.onSuccess(event, $self);
                        }

                        if (pluginInstanceStorage.allowQuickMode) {
                            if (pluginInstanceStorage.selected > 0) {
                                pluginInstanceStorage.quickMode = true;
                            }
                        }
                    },

                    reject: function (event) {
                        pluginInstanceStorage.selected--;

                        if (args.onReject) {
                            args.onReject(event, $self);
                        }

                        if (pluginInstanceStorage.selected == 0) {
                            pluginInstanceStorage.quickMode = false;
                        }
                    }
                }

                var handlers = {

                    success: function (event, forceReject) {
                        if (isSelected || forceReject) {
                            handlersSuccess.reject(event);
                        } else {
                            handlersSuccess.select(event);
                        }

                        isSelected = !isSelected;
                        isPreStart = false;

                        timeouts.mainDelayClear();
                    },


                    stop: function (event) {
                        clearingTimers(startTimer, timeouts.onStartDelayClear, event);
                        clearingTimers(timer, timeouts.mainDelayClear, event, true);
                    },


                    override: function (callback, preventFlag) {
                        return function (event) {
                            if (callback) {
                                callback(event, $self);
                            }

                            if (preventFlag) {
                                events.killEvent(event);
                            }
                        };
                    }
                };

                var events = {
                    killEvent: function (event) {
                        event.preventDefault();
                        return;
                    },

                    scrolling: function (event) {

                        if (isPreStart == false) {
                            handlers.stop(event);
                        }


                        if (isTapStart) {
                            isTapStart = false;
                        }
                    },

                    tapStart: function (event) {
                        isTapStart = true;
                        isTapStartStamp = performance.now();

                        var focusableElements = $self.find(':focusable');
                        var eventTarget = $(event.target);
                        
                        shouldIPreventClick = focusableElements.is(eventTarget);

                        // if `shouldIPreventClick == true` this timeout will be cleaned in `tapEnd` event
                        startTimer = setTimeout(function () {
                            event.preventDefault();
                            isPreStart = true;

                            if (args.onStart) {
                                args.onStart(event, $self);
                            }

                            timer = setTimeout(handlers.success, timeouts.mainDelay, event);
                            timeouts.onStartDelayClear();
                        }, timeouts.onStartDelay);
                    },

                    tapEnd: function (event) {
                        if (isTapStart) {
                            isTapStart = false;


                            if ((performance.now() - isTapStartStamp) <= 300) {
                                if (shouldIPreventClick) {
                                    handlers.stop(event);
                                    return;
                                }

                                if (pluginInstanceStorage.quickMode) {
                                    handlers.success(event);
                                }
                            }
                        }

                        if (event.cancelable) {
                            event.preventDefault();
                        }
                        handlers.stop(event);
                    }
                };

                $self.storage.elements.push({
                    timeouts: timeouts,
                    handlers: handlers,
                    handlersSuccess: handlersSuccess,
                    events: events
                });

                $self.on('click',
                    handlers.override(args.onClick, preventClick)
                );

                if (Modernizr.touchevents) {

                    $self.on('contextmenu',
                        handlers.override(args.onContext, preventContext)
                    );
                    $self.on('selectstart',
                        handlers.override(args.onSelect, preventSelect)
                    );
                }

                $self.on('touchmove', events.scrolling);
                $self.on('touchstart', events.tapStart);
                $self.on('touchend', events.tapEnd);
            }

            return {
                each: eachFunction,
                storage: pluginInstanceStorage,
                selectObject: function (index) {
                    pluginInstanceStorage.elements[index].handlers.success();
                },

                rejectObject: function (index) {
                    // force rejecting
                    pluginInstanceStorage.elements[index].handlers.success(true);
                }
            }
        }

        var ceLongtap = plugin();

        this.each(ceLongtap.each);

        return ceLongtap;

    }

})(jQuery);
