(function (factory) {
    "use strict";
    if (typeof define === 'function' && define.amd) {
        // AMD. Register as an anonymous module.
        define(['jquery'], factory);
    } else if (typeof exports === 'object' && typeof require === 'function') {
        // Browserify
        factory(require('jquery'));
    } else {
        // Browser globals
        factory(jQuery);
    }
}(function ($) {
    'use strict';

    var
        utils = (function () {
            return {
                escapeRegExChars: function (value) {
                    return value.replace(/[|\\{}()[\]^$+*?.]/g, "\\$&");
                },
                createNode: function (containerClass) {
                    var div = document.createElement('div');
                    div.className = containerClass;
                    div.style.position = 'absolute';
                    div.style.display = 'none';
                    return div;
                }
            };
        }()),

        keys = {
            ESC: 27,
            TAB: 9,
            RETURN: 13,
            LEFT: 37,
            UP: 38,
            RIGHT: 39,
            DOWN: 40
        };

    function UI(el, options) {
        var noop = $.noop,
            that = this,
            defaults = {
            };
        // Shared variables:
        that.element = el;
        that.el = $(el);
        // Initialize and set options:
        that.initialize();
        that.setOptions(options);
    }

    UI.Button = function () {
		
	};

    $.UI = UI;

	
    UI.prototype = {
        initialize: function () {
            var that = this;
        },
        setOptions: function (suppliedOptions) {
            var that = this,
                options = that.options;

            $.extend(options, suppliedOptions);
			
        },
		dispose: function () {
            var that = this;
        },
		addElement:function (el) {
		
		}
    };
    // Create chainable jQuery plugin:
    $.fn.UI = function (options, args) {
        var dataKey = 'ui';

        return this.each(function () {
            var inputElement = $(this),
                instance = inputElement.data(dataKey);

            if (typeof options === 'string') {
                if (instance && typeof instance[options] === 'function') {
                    instance[options](args);
                }
            } else {
                // If instance already exists, destroy it:
                if (instance && instance.dispose) {
                    instance.dispose();
                }
                instance = new UI(this, options);
                inputElement.data(dataKey, instance);
            }
        });
    };
    $.fn.addUIElement = function (options) {
		if (typeof option !== "object" || option==undefined) return;
		//var element = getElement(option);
		console.log("asd"+this[0]);//.appendChild(element);
		//return element;
    };
}));
