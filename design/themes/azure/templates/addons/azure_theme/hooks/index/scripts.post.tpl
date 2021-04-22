<script type="text/javascript">
	(function($){
		var methods = {
			init: function(params) {
				return this.each(function() {
					var params = params || {
						top: $(this).data('ceTop') ? $(this).data('ceTop') : 0,
						padding: $(this).data('cePadding') ? $(this).data('cePadding') : 0
					};
					var self = $(this);

					$(window).scroll(function () {
						if ($(window).scrollTop() > params.top) {
							$(self).addClass('sticky-scroll');
							$(self).css( { 'top': params.padding + 'px' });
						} else {
							$(self).removeClass('sticky-scroll');
							$(self).css( { 'top': '' } );
						}
					});

				});
			}
		};

		$.fn.ceStickyScrollClass = function(method) {
			if (methods[method]) {
				return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
			} else if ( typeof method === 'object' || ! method ) {
				return methods.init.apply(this, arguments);
			} else {
				$.error('ty.stickyScroll: method ' +  method + ' does not exist');
			}
		};
	})($);
	(function(_, $){
	    $(document).on('click', '.cm-slide', function(e) {
	    	e.preventDefault();
	    	var jelm = $(e.target);
	    	var p_elm = (jelm.parents('.cm-slide').length) ? jelm.parents('.cm-slide:first') : (jelm.prop('id') ? jelm : jelm.parent());
	    	var id, prefix;
            if (p_elm.prop('id')) {
                prefix = p_elm.prop('id').match(/^(on_|off_|sw_)/)[0] || '';
                id = p_elm.prop('id').replace(/^(on_|off_|sw_)/, '');
            }
            var container = $('#' + id);
            
            var flag = (prefix == 'on_') ? false : (prefix == 'off_' ? true : (container.is(':visible') ? true : false));

            if (p_elm.hasClass('cm-uncheck')) {
                $('#' + id + ' [type=checkbox]').prop('disabled', flag);
            }

			if (flag && !container.hasClass('visible')) {
				$('#sw_' + container.prop('id')).toggleClass('open');
			} else {
				container.toggleClass('visible');
			}

            if (prefix == 'sw_') {
			    if (p_elm.hasClass('open')) {
			        p_elm.removeClass('open');

			    } else if (!p_elm.hasClass('open')) {
			        p_elm.addClass('open');
			    }
			}
	    });
	})(Tygh, Tygh.$);


	(function (_, $) {
		$(document).ready(function(){
			elm = $('.sticky-menu');
			if (elm.length) {
			offset = elm.offset();
			height = elm.height();
			elm.data('ceTop', offset.top);
			elm.parent().css('min-height', height);
			elm.ceStickyScrollClass();

			$('[id^="slide_"]').each(function() {
				var combination_elm = $(this);
				$.ceEvent('on', 'ce.switch_' + combination_elm.prop('id'), function(flag) {
					
// 					combination_elm.css('display', '')
// 					if (flag && !combination_elm.hasClass('visible')) {
// 						$('#sw_' + combination_elm.prop('id')).toggleClass('open');
// 					} else {
// 						combination_elm.toggleClass('visible');
// 					}
				});
			});
			}
		});



        var methods = {

            init: function(params) {

                var default_params = {
                    events: {
                        def: 'mouseover, mouseout',
                        input: 'focus, blur'
                    },
                    layout: '<div><span class="tooltip-arrow"></span></div>'
                };

                $.extend(default_params, params);

                return this.each(function() {
                    var elm = $(this);
                    var params = default_params;

                    if (elm.data('tooltip')) {
                        return false;
                    }

	                params.position = 'bottom center';
	                params.tipClass = 'tooltip arrow-down middle-arrow';
	                params.offset = [12, 0];

                    elm.tooltip(params).dynamic({
                        right: {},
                        left: {}
                    });

                    //hide tooltip before remove
                    elm.on("remove", function() {
                        $(this).trigger('mouseout');
                    });
                });
            }
        };

        $.fn.ceAzureTooltip = function(method) {
            if (methods[method]) {
                return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
            } else if ( typeof method === 'object' || ! method ) {
                return methods.init.apply(this, arguments);
            } else {
                $.error('ty.tooltip: method ' +  method + ' does not exist');
            }
        };



	    $.ceEvent('on', 'ce.commoninit', function() {
            $(document).on('mouseover', '.cm-azure-tooltip[title]', function() {
                if (!$(this).data('tooltip')) {
                    $(this).ceAzureTooltip();
                }
                $(this).data('tooltip').show();
            });
    	});
	})(Tygh, Tygh.$);

    /*
     *
     * Tooltips
     *
     */
     (function($){


    })($);
$(document).ready(function(){
var $voiceTrigger = $(".ty-search-block");
var $searchForm = $(".ty-search-block > form");
var $searchInput = $("#search_input");
var $result = $("#result");
/*  set Web Speech API for Chrome or Firefox */
window.SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

/* Check if browser support Web Speech API, remove the voice trigger if not supported */
if (window.SpeechRecognition) {

    /* setup Speech Recognition */
    var recognition = new SpeechRecognition();
    recognition.interimResults = true;
    recognition.lang = 'ru-RU';
    recognition.addEventListener('result', _transcriptHandler);

    recognition.onerror = function(event) {
        console.log(event.error);

        /* Revert input and icon CSS if no speech is detected */
        if(event.error == 'no-speech'){
            $voiceTrigger.removeClass('active');
            $searchInput.attr("placeholder", "Поиск...");
        }
    }
} else {
    $voiceTrigger.remove();
}

jQuery(document).ready(function(){

    /* Trigger listen event when our trigger is clicked */
    $voiceTrigger.on('click touch', listenStart);
});

/* Our listen event */
function listenStart(e){
    e.preventDefault();
    /* Update input and icon CSS to show that the browser is listening */
    $searchInput.attr("placeholder", "Говорите...");
    $voiceTrigger.addClass('active');
    /* Start voice recognition */
    recognition.start();
}

/* Parse voice input */
function _parseTranscript(e) {
    return Array.from(e.results).map(function (result) { return result[0] }).map(function (result) { return result.transcript }).join('')
}

/* Convert our voice input into text and submit the form */
function _transcriptHandler(e) {
    var speechOutput = _parseTranscript(e)
    $searchInput.val(speechOutput);
    //$result.html(speechOutput);
    if (e.results[0].isFinal) {
        $searchForm.submit();
    }
}
});
</script>