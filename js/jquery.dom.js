;(function($, window, document, undefined) {
	"use strict";
	function getElement(e) {
		var _tagName = (typeof e.t === "string")?e.t:"div", _element;
		_element = document.createElement(_tagName);
		if (typeof e.onClick === "function") _element.onclick = e.onClick;
		if (typeof e.onChange === "function") _element.onchange = e.onChange;
		if (typeof e.onInput === "function") _element.oninput = e.onInput;
		if (typeof e.onAbort === "function") _element.onabort = e.onAbort;
		if (typeof e.onBeforeInput === "function") _element.onbeforeinput = e.onBeforeInput;
		if (typeof e.onLoad === "function") _element.onload = e.onLoad;
		if (typeof e.onUnload === "function") _element.onunload = e.onUnload;
		if (typeof e.onWheel === "function") _element.onwheel = e.onWheel;
		if (typeof e.onError === "function") _element.onerror = e.onError;
		if (typeof e.onFocus === "function") _element.onfocus = e.onFocus;
		if (typeof e.onFocusIn === "function") _element.onfocusin = e.onFocusIn;
		if (typeof e.onFocusOut === "function") _element.onfocusout = e.onFocusOut;
		if (typeof e.onKeyDown === "function") _element.onkeydown = e.onKeyDown;
		if (typeof e.onKeyPress === "function") _element.onkeypress = e.onKeyPress;
		if (typeof e.onKeyUp === "function") _element.onkeyup = e.onKeyUp;
		if (typeof e.onMouseDown === "function") _element.onmousedown = e.onMouseDown;
		if (typeof e.onMouseEnter === "function") _element.onmouseenter = e.onMouseEnter;
		if (typeof e.onMouseLeave === "function") _element.onmouseleave = e.onMouseLeave;
		if (typeof e.onMouseMove === "function") _element.onmousemove = e.onMouseMove;
		if (typeof e.onMouseOut === "function") _element.onmouseout = e.onMouseOut;
		if (typeof e.onMouseOver === "function") _element.onmouseover = e.onMouseOver;
		if (typeof e.onMouseUp === "function") _element.onmouseup = e.onMouseUp;
		if (typeof e.onResize === "function") _element.onresize = e.onResize;
		if (typeof e.onScroll === "function") _element.onscroll = e.onScroll;
		if (typeof e.onSelect === "function") _element.onselect = e.onSelect;
		if (typeof e.onBlur === "function") _element.onblur = e.onblur;
		if (typeof e.onCompositionStart === "function") _element.oncompositionstart = e.onCompositionStart;
		if (typeof e.onCompositionUpdate === "function") _element.oncompositionupdate = e.onCompositionUpdate;
		if (typeof e.onCompositionEnd === "function") _element.oncompositionend = e.onCompositionEnd;
		if (typeof e.onDblClick === "function") _element.ondblclick = e.ondblclick;
		
		if (typeof e.html === "string") $(_element).html(e.html);
		if (typeof e.text === "string") $(_element).text(e.text);
		if (typeof e.href === "string") _element.href = e.href;
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
