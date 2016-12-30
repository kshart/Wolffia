;(function($, window, document, undefined) {
	"use strict";
	function getElement(e) {
		var _tagName = (typeof e.t === "string")?e.t:"div", _element;
		_element = document.createElement(_tagName);
		if (typeof e.onClick === "function") _element.onclick = e.onClick;
		if (typeof e.onChange === "function") _element.onchange = e.onChange;
		if (typeof e.onInput === "function") _element.oninput = e.onInput;
		if (typeof e.html === "string") $(_element).html(e.html);
		if (typeof e.text === "string") _element.textContent = e.text;
		if (typeof e.id === "string") _element.id = e.id;
		if (typeof e["class"] === "string") _element.className = e["class"];
		
		for(var i in e.attr) _element.setAttribute(i, e.attr[i]);
		for(var i in e.child) _element.appendChild(getElement(e.child[i]));
		e.that = _element;
		return _element;
	}
	$.dom = function (option) {
		if (typeof option !== "object" || option==undefined) return;
		var element = getElement(option);
		return element;
	};
	$.fn.dom = function (option) {
		if (typeof option !== "object" || option==undefined) return;
		var element = getElement(option);
		this[0].appendChild(element);
		return element;
	};
})(jQuery, window, document);
