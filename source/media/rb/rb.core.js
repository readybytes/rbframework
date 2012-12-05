/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		RB Framework
* @subpackage	UI
* @contact 		support@readybytes.in
*/

/*-----------------------------------------------------------
  Javascript writing standards - 
  - Pack you code in 
  		(function($){
  				// Your code here
  				// Your code here
  		})(rb.jQuery); 
  		
  	and use $ as usually.
-----------------------------------------------------------*/
if (typeof(rb)=='undefined')
{
	var rb = {
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
  rb.ui.dialog.create  = create a dialog, fill with ajax data
  rb.ui.dialog.button  = add buttons on dialog
  rb.ui.dialog.title   = set title
  rb.ui.dialog.height  = set height
  rb.ui.dialog.close   = close dialog  
--------------------------------------------------------------*/
rb.ui = {};
rb.ui.dialog = { 
	create : function(call, winTitle, winContentWidth, winContentHeight){
		//a workaround for a flaw in the demo system (http://dev.jqueryui.com/ticket/4375), ignore!
		$("#rbWindowContent:ui-dialog").dialog( "destroy" );
		
		//
		if(winTitle == null) winTitle = 'Title';
		if(winContentWidth == null) winContentWidth = 'auto';
		if(winContentHeight == null) winContentHeight = 'auto';
		
		// create a empty-div & show a dialog
		$('#rbWindowContent').remove();
		$('<div id="rbWindowContent" class="rbGlobalDialog loading"></div>')
				.addClass('new').appendTo('body');
		$('#rbWindowContent').dialog({
			autoOpen: false,
			title : winTitle,
			width : winContentWidth,
			height: winContentHeight,
			modal : true ,
			close: function(event, ui) { 
					$('.rbGlobalDialog').remove(); 
				}
		});
		
		if(call.url === null){
			$('#rbWindowContent').dialog('open');
			$('#rbWindowContent').removeClass('loading');
			if(call.data !== null){
				$('#rbWindowContent').append(call.data);
			}
			return;
		}
		
		var dialogCallback = function(result){
			// process ajax content
			rb.ajax.default_success_callback(result);
			
			//remove class loading
			$('#rbWindowContent').removeClass('loading');
			
			// open dialog
			$('#rbWindowContent').dialog('open');
		};
		
		var dialogCallbackIframe = function(event){
			// open dialog
			$('#rbWindowContent').dialog('open');
			$('#rbWindowContent').removeClass('loading');
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
			rb.iframe.show(call, '#rbWindowContent', dialogCallbackIframe);
			return this;
		}
		
		// call ajax
		rb.ajax.go(call.url, call.data, dialogCallback);
	},
	
	button : function(actions){
		for(var i=0;i<actions.length;i++) {
			actions[i].click = eval("(function(){" + actions[i].click + ";})" );
		}
		
		$('#rbWindowContent').dialog("option", "buttons", actions);
	},
	
	title : function(title){
		$('#rbWindowContent').dialog("option", "title", title);
	},
	
	close : function(title){
		$('#rbWindowContent').dialog('close');
	},
	
	height : function(height){
		$('#rbWindowContent').dialog("option", "height", height);
	},
	
	width : function(width){
		$('#rbWindowContent').dialog("option", "width", width);
	},

	autoclose : function($time){
		setTimeout(function(){
			$("#rbWindowContent").dialog('close')
		}, $time);
	}
};

/*--------------------------------------------------------------
  URL related work
--------------------------------------------------------------*/
rb.route = {
	url : function(url){
				// already a complete URL
				if(url.indexOf('http://') === -1){
						// is it already routed URL without http ?
					  var base2_url_index = url.indexOf(rb_vars['url']['base_without_scheme']);
					  // only add if, its not routed URL
					  if(base2_url_index === -1 ){
						  url = rb_vars['url']['base'] + url;
					  }
				}
				
				return url;
	}
};

/*--------------------------------------------------------------
  Ajax related works
--------------------------------------------------------------*/

rb.ajax = {	
		
	//RBFW_TODO : replace via jQuery code
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
	},
	
	default_error_callback : function (error){
		//RBFW_TODO : log to console
		alert("An error has occured\n"+error);
	},
	
	default_success_callback : function (result){
		
		//RBFW_TODO : log to console
		
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
				rb.ajax.create(id,property, data);
				break;

			case 'rm':
				rb.ajax.remove(id);
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
	
	/*
	 * url : URL to call
	 * data : array / json / string / object
	 * */
	go : function (url, data, successCallback, errorCallback, timeout){
		// RBFW_TODO : If ajax not available, handle by Iframes 
		
		// timeout 60 seconds
		if(timeout == null) timeout = 600000;
		if(errorCallback == null) errorCallback = rb.ajax.default_error_callback;
		if(successCallback == null) successCallback = rb.ajax.default_success_callback;

		// properly oute the url
		ajax_url = rb.route.url(url) + '&isAjax=true';
	
		//execute ajax
		// in jQ1.5+ first argument is url
		$.ajax(ajax_url, {
			type	: "POST",
			cache	: false,
			data	: data,
			timeout	: timeout,
			success	: function(msg){ rb.ajax.success(msg,successCallback,errorCallback); },
			error	: function(Request, textStatus, errorThrown){rb.ajax.error(Request, textStatus, errorThrown, errorCallback);}
		});
	}
};



rb.iframe = {

	show:function (call, appendTo,onLoadCallback){
		
		if(onLoadCallback == null) onLoadCallback = this.process;
		if(appendTo == null) appendTo = '#rbWindowContent';
		
		if(typeof call.classes === "undefined"){ 
			call.classes = '';
		} 
		
		if(typeof call.id === "undefined"){ 
			call.id = '';
		}
		
		$iframe = $('<iframe id="'+call.id+'" class="rb-iframe '+call.classes+'" frameborder="0" scrolling="auto" width="98%" height="90%" >');
		$iframe.load(onLoadCallback).appendTo(appendTo);
				
		// properly output the url
		url = rb.route.url(call.url);
		
		url += '&' + $.param(call.data);
		$iframe.attr('src',url);
		return $iframe;
	},
	
	process : function(){
		
	}
};


/*---------------------------------------------------------
Joomla function available through rb.cms framework 
---------------------------------------------------------*/
rb.joomla = {};

rb.joomla.text = {
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
rb.cms = rb.joomla;


//Document ready
$(document).ready(function(){

	// load translation
	rb.cms.text.load(rb.joomla.text.strings);
});

//ENDING :
//Scoping code for easy and non-conflicting access to $.
//Should be last line, write code above this line.
})(rb.jQuery);