<?php

class PageBuilder {
	function PageBuilder(doctype, asynch, lang);
	
	function addMETA();
	function addJS();
	function addCSS();
	
	function write();
	function clear();
	function flush();
};
class PageBuilderModule {
	function PageBuilderModule();
	
	function getMETA();
	function getJS();
	function getCSS();
	
	function getContent();
};