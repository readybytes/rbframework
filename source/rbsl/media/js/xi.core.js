/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		PayPlans
* @subpackage	UI
* @contact 		payplans@readybytes.in
*/	

/*-----------------------------------------------------------
  Javascript writing standards - 
  - Pack you code in (function($){})(xi.jQuery); and use $ as usually.
-----------------------------------------------------------*/
if (typeof(xi)=='undefined')
{
	var xi = {
		jQuery: window.jQuery,
		extend: function(obj){
			this.jQuery.extend(this, obj);
		}
	}
}


(function($){
// START : 	
// Scoping code for easy and non-conflicting access to $.
// Should be first line, write code below this line.	
	

/*--------------------------------------------------------------
  UI related works
  xi.ui.dialog.create  = create a dialog, fill with ajax data
  xi.ui.dialog.button  = add buttons on dialog
  xi.ui.dialog.title   = set title
  xi.ui.dialog.height  = set height
  xi.ui.dialog.close   = close dialog  
--------------------------------------------------------------*/
xi.ui = {};
xi.ui.dialog = { 
	create : function(call, winTitle, winContentWidth, winContentHeight){
		//a workaround for a flaw in the demo system (http://dev.jqueryui.com/ticket/4375), ignore!
		$("#xiWindowContent:ui-dialog").dialog( "destroy" );
		
		//
		if(winTitle == null) winTitle = 'Title';
		if(winContentWidth == null) winContentWidth = 'auto';
		if(winContentHeight == null) winContentHeight = 'auto';
		
		// create a empty-div & show a dialog
		$('#xiWindowContent').remove();
		$('<div id="xiWindowContent" class="xiGlobalDialog loading"></div>')
				.addClass('new').appendTo('body');
		$('#xiWindowContent').dialog({
			autoOpen: false,
			title : winTitle,
			width : winContentWidth,
			height: winContentHeight,
			modal : true ,
			close: function(event, ui) { 
					$('.xiGlobalDialog').remove(); 
				}
		});
		
		if(call.url === null){
			$('#xiWindowContent').dialog('open');
			$('#xiWindowContent').removeClass('loading');
			if(call.data !== null){
				$('#xiWindowContent').append(call.data);
			}
			return;
		}
		
		var dialogCallback = function(result){
			// process ajax content
			xi.ajax.default_success_callback(result);
			
			//remove class loading
			$('#xiWindowContent').removeClass('loading');
			
			// open dialog
			$('#xiWindowContent').dialog('open');
		};
		
		var dialogCallbackIframe = function(event){
			// open dialog
			$('#xiWindowContent').dialog('open');
			$('#xiWindowContent').removeClass('loading');
			// no scrollbar
			$(this).contents().find('html').css('overflow-y', 'auto !important');
			// no background
			$(this).contents().find('body').css('background','transparent !important;');
			$(this).contents().find('.pp-component').addClass('pp-iframe');
		};
		
		if(typeof call.iframe === undefined){
			call.iframe = false;
		}
		
		if(call.iframe){
			// show iframe
			xi.iframe.show(call, '#xiWindowContent', dialogCallbackIframe);
			return this;
		}
		
		// call ajax
		xi.ajax.go(call.url, call.data, dialogCallback);
	},
	
	button : function(actions){
		for(var i=0;i<actions.length;i++) {
			actions[i].click = eval("(function(){" + actions[i].click + ";})" );
		}
		
		$('#xiWindowContent').dialog("option", "buttons", actions);
	},
	
	title : function(title){
		$('#xiWindowContent').dialog("option", "title", title);
	},
	
	close : function(title){
		$('#xiWindowContent').dialog('close');
	},
	
	height : function(height){
		$('#xiWindowContent').dialog("option", "height", height);
	},
	
	width : function(width){
		$('#xiWindowContent').dialog("option", "width", width);
	},

	autoclose : function($time){
		setTimeout(function(){
			$("#xiWindowContent").dialog('close')
		}, $time);
	}
};

/*--------------------------------------------------------------
  URL related work
--------------------------------------------------------------*/
xi.route = {
	url : function(url){
				// already a complete URL
				if(url.indexOf('http://') === -1){
						// is it already routed URL without http ?
					  var base2_url_index = url.indexOf(xi_vars['url']['base_without_scheme']);
					  // only add if, its not routed URL
					  if(base2_url_index === -1 ){
						  url = xi_vars['url']['base'] + url;
					  }
				}
				
				return url;
	}
};

/*--------------------------------------------------------------
  Ajax related works
--------------------------------------------------------------*/

xi.ajax = {	
		
	//XITODO : replace via jQuery code
	create : function(sParentId, sTag, sId){
		var objParent = this.$(sParentId);
		objElement = document.createElement(sTag);
		objElement.setAttribute('id',sId);
		if(objParent){
			objParent.appendChild(objElement);
		}
	},

	remove : function(sId){
		$(sId).remove();
//		objElement = this.$(sId);
//		if (objElement && objElement.parentNode && objElement.parentNode.removeChild)
//		{
//			objElement.parentNode.removeChild(objElement);
//		}
	},
	
	default_error_callback : function (error){
		//XITODO : log to console
		alert("An error has occured\n"+error);
	},
	
	default_success_callback : function (result){
		
		//XITODO : log to console
		
		// we now have an array, that contains an array.
		for(var i=0; i<result.length;i++){

			var cmd 		= result[i][0];
			var id			= result[i][1];
			var property 	= result[i][2];
			var data 		= result[i][3];

			var objElement = $(id);

			switch(cmd){
			case 'as': 	// assign or clear
				if(objElement){
					if(property == 'innerHtml' || property == 'innerHTML'){
						$('#'+id).html(data);
					}else if(property == 'replaceWith'){
						$('#'+id).replaceWith(data);
					}else{
						eval("objElement."+property+"=  data \; ");
					}
				}

				break;

			case 'al':	// alert
				if(data){
					alert(data);}
				break;

			case 'ce':
				xi.ajax.create(id,property, data);
				break;

			case 'rm':
				xi.ajax.remove(id);
				break;

			case 'cs':	// call script
				var scr = id + '(';
				if($.isArray(data)){
					scr += '(data[0])';
					for (var l=1; l<data.length; l++) {
						scr += ',(data['+l+'])';
					}
				} else {
					scr += 'data';
				}
				scr += ');';
				eval(scr);
				break;

			default:
				alert("Unknow command: " + cmd);
			}
		}
	},

	error : function(Request, textStatus, errorThrown, errorCallback) {
		var message = '<strong>AJAX Loading Error</strong><br/>HTTP Status: '+Request.status+' ('+Request.statusText+')<br/>';
		message = message + 'Internal status: '+textStatus+'<br/>';
		message = message + 'XHR ReadyState: ' + Request.readyState + '<br/>';
		message = message + 'Raw server response:<br/>'+Request.responseText;
		errorCallback(message);	
	},
	
	success : function(msg, successCallback, errorCallback) {
		// Initialize
		var junk = null;
		var message = "";
		
		// Get rid of junk before the data
		var valid_pos = msg.indexOf('###');
		var valid_last_pos = msg.lastIndexOf('###');
		if( valid_pos == -1 ) {
			// Valid data not found in the response
			msg = 'Invalid AJAX data: ' + msg;
			errorCallback(msg);
			return;
		}
		
		// get message between ###<----->### second argument is length
		message = msg.substr(valid_pos+3, valid_last_pos-(valid_pos+3)); 
		
		try {
			var data = JSON.parse(message);
		}catch(err) {
			var msg = err.message + "\n<br/>\n<pre>\n" + message + "\n</pre>";
			errorCallback(msg);
			return;
		}
		
		// Call the callback function
		successCallback(data);
	},
	
	//deperecated 
	call  : function (url){
		var arg = "&";
		var data = new Array();
		if(arguments.length > 1){
			for(var i=1; i < arguments.length; i++){
				var a = arguments[i];
				arg += 'arg' + i + '=' + encodeURIComponent(JSON.stringify(a)) + '&';
				//collect data serially
				data[i-1]=arguments[i];
			}
		}
		
		arg += "arg_count=" + arguments.length;
		
		//XITODO : remove it in 2.1, support deprecated calls
		url += arg;
		
		xi.ajax.go(url,data);
	},
	
	/*
	 * url : URL to call
	 * data : array / json / string / object
	 * */
	go : function (url, data, successCallback, errorCallback, timeout){
		// XITODO : If ajax not available, handle by Iframes 
		
		// timeout 60 seconds
		if(timeout == null) timeout = 600000;
		if(errorCallback == null) errorCallback = xi.ajax.default_error_callback;
		if(successCallback == null) successCallback = xi.ajax.default_success_callback;

		// properly oute the url
		ajax_url = xi.route.url(url) + '&isAjax=true';
	
		//execute ajax
		// in jQ1.5+ first argument is url
		$.ajax(ajax_url, {
			type	: "POST",
			cache	: false,
			data	: data,
			timeout	: timeout,
			success	: function(msg){ xi.ajax.success(msg,successCallback,errorCallback); },
			error	: function(Request, textStatus, errorThrown){xi.ajax.error(Request, textStatus, errorThrown, errorCallback);}
		});
	}
};



xi.iframe = {

	show:function (call, appendTo,onLoadCallback){
		
		if(onLoadCallback == null) onLoadCallback = this.process;
		if(appendTo == null) appendTo = '#xiWindowContent';
		
		if(typeof call.classes === "undefined"){ 
			call.classes = '';
		} 
		
		if(typeof call.id === "undefined"){ 
			call.id = '';
		}
		
		$iframe = $('<iframe id="'+call.id+'" class="pp-iframe '+call.classes+'" frameborder="0" scrolling="auto" width="98%" height="90%" >');
		$iframe.load(onLoadCallback).appendTo(appendTo);
				
		// properly oute the url
		url = xi.route.url(call.url);
		
		url += '&' + $.param(call.data);
		$iframe.attr('src',url);
		return $iframe;
	},
	
	process : function(){
		
	}
};



/*---------------------------------------------------------
Joomla function available through xi.cms framework 
---------------------------------------------------------*/
xi.joomla = {};

xi.joomla.text = {
	// string holder
	strings: {
	},
	
	// translate
	"_": function(key, def) {
		return typeof this.strings[key.toUpperCase()] !== "undefined" ? this.strings[key.toUpperCase()] : def;
	},
	
	// add all keys
	load: function(object) {
		for (var key in object) {
			this.strings[key.toUpperCase()] = object[key];
		}
		return this;
	}
};

/*---------------------------------------------------------
	Javascript interface to underline framework 
---------------------------------------------------------*/
xi.cms = xi.joomla;


//Document ready
$(document).ready(function(){

	// load translation
	xi.cms.text.load(xi_strings);
});

//ENDING :
//Scoping code for easy and non-conflicting access to $.
//Should be last line, write code above this line.
})(xi.jQuery);

//1.4 : Backward Compatibility
xiajax 		= xi.ajax;
xi.ui.dialog.close= xi.ui.dialog.close;