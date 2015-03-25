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

		if(winTitle == null) winTitle = 'Title';
		if(winContentWidth == null) winContentWidth = 'auto';
		if(winContentHeight == null) winContentHeight = 'auto';
		
		// create a empty-div & show a dialog
		$('#rbWindowContent').remove();

		//XITODO : loading class required or not
		$('<div id="rbWindowContent" class="modal hide fade loading" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"></div>') 
				.addClass('new').appendTo('.rb-wrap');
		
		// add title, body and footer block so that sequence can be maintained
		$('<div id="rbWindowTitle"></div>').appendTo('#rbWindowContent');
		$('<div id="rbWindowBody"></div>').appendTo('#rbWindowContent');
		$('<div id="rbWindowFooter"></div>').appendTo('#rbWindowContent');
		
		// set the title
		this.title(winTitle);
		
		$('#rbWindowContent').on('show', function () {
			var modal = $(this);
			
			//@RBTODO:: Should be responsive
			// Customize height and width.			
			// Centralize modal window after set required width and height.
			modal.css({	width:winContentWidth, height:winContentHeight });
				 //.css('margin-top', (modal.outerHeight() / 2) * -1)				 
		         //.css('margin-left', (modal.outerWidth() / 2) * -1);
			
		    return this;
		    
		});
		
		// on hiding the popup, remove the div#rbWindowContent also 
		$('#rbWindowContent').on('hidden', function() {
			$('#rbWindowContent').remove();
		});
		
		// show the modal
		$('#rbWindowContent').modal('show');
		
		// call ajax
		if(call != null){
			rb.ajax.go(call.url, call.data);
		}
		return true;
	},
	
	button : function(actions){
		// empty previous action buttons
		$('#rbWindowFooter').html('');
		$('<div class="modal-footer"></div>').appendTo('#rbWindowFooter');

		for(var i=0;i<actions.length;i++) {
			actions[i].click = eval("(function(){" + actions[i].click + ";})" );
			var button = '<button class="'+actions[i].classes+'" '+actions[i].attr+'>'+actions[i].text+'</button>';
			$(button).bind('click', actions[i].click).appendTo('#rbWindowFooter > .modal-footer');			
		}		
	},
	
	body : function(body){
		// empty previous body content
		$('#rbWindowBody').html('');
		if(body != null && body.length > 0){
			$('<div class="modal-body"><p>'+body+'</p></div>').appendTo('#rbWindowBody');	
		}
	},
	
	title : function(title){
		// empty previous title
		$('#rbWindowTitle').html('');
		
		// show the header in case of title is not empty
		if(title != null && title.length > 0){
			$('<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button><h3 id="myModalLabel">'+title+'</h3></div>')
				.appendTo('#rbWindowTitle');	
		}
	},
	
	close : function(title){
		$('#rbWindowContent').modal('hide');
	},
	
	height : function(height){
		$('#rbWindowBody').css("height", height);
	},

	width : function(width){
		$('#rbWindowContent').css("width", width);
	},

	autoclose : function($time){
		setTimeout(function(){
			$('#rbWindowContent').modal('hide')
		}, $time);
	}
};

/*--------------------------------------------------------------
  URL related work
  	# url.modal		:	Open a url in modal window
   	# url.redirect	:	Redirect current window to new url
--------------------------------------------------------------*/
//deprecated and move into rb.url
rb.route = {
	url : function(url){
				return rb.url.route(url)
	}
};


rb.url = {
	  	modal	: 	function( theurl, options, windowWidth, windowHeight)
	  	{
			var ajaxCall = {'url':theurl, 'data': {}, 'iframe': false};
				
			if(options) {
			  ajaxCall = {'url':theurl, 'data':options.data, 'iframe' : false};
			}
			
			if (!windowWidth)  { windowWidth = 'auto';	 }
			
			if (!windowHeight) { windowHeight = 'auto'; }

			rb.ui.dialog.create(ajaxCall, '', windowWidth, windowHeight);
		},
			
		redirect	:	function(url) 
		{
			document.location.href=url;
		},
		
		route	:	function(url)
		{
			// already a complete URL
			// check if url does not contain root url. Base url itself contain root.
			// root is being checked because, admin side can use front end url.
			if(url.indexOf(rb_vars['url']['root']) === -1) {
					// is it already routed URL without http ?
				  var base2_url_index = url.indexOf(rb_vars['url']['base_without_scheme']);
				  // only add if, its not routed URL
				  if(base2_url_index > -1){
					 url = url.replace(rb_vars['url']['base_without_scheme'], '');
				  }
				  
				  var containsSlash = url.substr(0,1);
				  if (containsSlash == '/') {
					  url = url.substr(1);
				  }
				  
				  url = rb_vars['url']['base'] + url;
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

			switch(cmd){
			case 'as': 	// assign or clear
				var objElement = $(id);
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
	
	success : function(msg, successCallback, errorCallback) 
	{
		var data = false;
		var exception = false;
		
		try {
			data = rb.ajax.junkFilter(msg);
		} catch(err) {
			data = err.message + "\n<br/>\n<pre>\n" + message + "\n</pre>";
			exception = true;
		}
		
		if (!data || exception){
			return errorCallback(data);
		}
		
		// Call the callback function
		return successCallback(data);
	},
	
	/**
	 * Invoke to clear data (remove warnings and error) from response
	 * response always availble betwwen '###' so we will propely formate it.
	 *   
	 */
	junkFilter : function( response_data) 
	{
		// Get rid of junk before the data
		var valid_pos = response_data.indexOf('###');
		var valid_last_pos = response_data.lastIndexOf('###');
		
		if( valid_pos == -1 ) {
			return false;
		}
		
		// get message between ###<----->### second argument is length
		meaningful_response_data = response_data.substr(valid_pos+3, valid_last_pos-(valid_pos+3)); 
		
		try {
			meaningful_response_data = $.parseJSON(meaningful_response_data);
			
		} catch(e) {
			console.log({'exception_was : ': e});
			return false;
		}
			
		return meaningful_response_data;
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

		// properly route the url
		ajax_url = rb.url.route(url) + '&format=ajax';
		
		var spinner_selector = false;
		// before ajax start, check any spinner selector availble or not if yes then on it
		if ( data && typeof data['spinner_selector'] != "undefined" &&  $(data['spinner_selector']).length > 0 ) {
			spinner_selector = data['spinner_selector'];
			// remove from data otherwise it will be post
			delete  data['spinner_selector'];
			//show spinner
			$(spinner_selector).show();
		}	
		
		//execute ajax
		// in jQ1.5+ first argument is url
		$.ajax(ajax_url, {
			type	: "POST",
			cache	: false,
			data	: data,
			timeout	: timeout,
			success	: function(msg) 
							{
								// stop spinner
								if ( spinner_selector ) {
									$(spinner_selector).hide();
								}
								
								rb.ajax.success(msg,successCallback,errorCallback); 
							},
			error	: function(Request, textStatus, errorThrown)
							{
								// stop spinner
								if ( spinner_selector ) {
									$(spinner_selector).hide();
								}
								
								rb.ajax.error(Request, textStatus, errorThrown, errorCallback);
							}
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
		url = rb.url.route(call.url);
		
		url += '&' + $.param(call.data);
		$iframe.attr('src',url);
		return $iframe;
	},
	
	process : function(){
		
	}
};


//Document ready
$(document).ready(function(){

	// load timeago
	$('.rb-timeago').timeago();
});

//ENDING :
//Scoping code for easy and non-conflicting access to $.
//Should be last line, write code above this line.
})(rb.jQuery);


