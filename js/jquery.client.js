;(function($, window, document, undefined) {
	"use strict";
	var plaginName = "ClientIn",
		defaults = {
			createElement:function (parent, item, data) {
				console.log("ClientIn.createElement", parent, item);
			},
			validate:function (data) {
				return data;
			},
			beforeCreateElements:function (parent, data) {
				
			}
		};
	function ClientIn(element, option) {
		this.element = element;
		this.options = $.extend({}, defaults, option);
		
		this._defaults = defaults;
		this._name = plaginName;
		this.init();
	};
	ClientIn.prototype.init = function () {
		console.log("ClientIn.prototype.init");
		
	};
	ClientIn.prototype.createLink = function (url, json, option) {
		var self = this;
		$.post(url, JSON.stringify(json), function(data, status, jqXNR){
			//data = {arr:[1, 2, 4]};
			if (typeof(data) == "string") {
				data = JSON.parse(data);
			}else if (data instanceof Object) {
			}else{
				return;
			}
			data = self.options.validate(data);
			if (data === false) return;
			var insertTarget = self.options.beforeCreateElements(self.element, data);
			for(var i in data.arr) {
				self.options.createElement(insertTarget, data.arr[i], data);
			}
		});
	};
	$.fn.ClientIn = function (option) {
		return this.each(function (){
			if (!$.data(this, "plagin_ClientIn")) $.data(this, "plagin_ClientIn", new ClientIn(this, option))
		});
	};
	$.fn.createLink = function (url, json) {
		return this.each(function (){
			if (!$.data(this, "plagin_ClientIn")) return;
			$.data(this, "plagin_ClientIn").createLink(url, json);
			
			
		});
	};
})(jQuery, window, document);