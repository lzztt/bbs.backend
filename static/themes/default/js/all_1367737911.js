/*!
 * jQuery Cookie Plugin v1.3.1
 * https://github.com/carhartl/jquery-cookie
 *
 * Copyright 2013 Klaus Hartl
 * Released under the MIT license
 */
(function (factory) {
	if (typeof define === 'function' && define.amd) {
		// AMD. Register as anonymous module.
		define(['jquery'], factory);
	} else {
		// Browser globals.
		factory(jQuery);
	}
}(function ($) {

	var pluses = /\+/g;

	function raw(s) {
		return s;
	}

	function decoded(s) {
		return decodeURIComponent(s.replace(pluses, ' '));
	}

	function converted(s) {
		if (s.indexOf('"') === 0) {
			// This is a quoted cookie as according to RFC2068, unescape
			s = s.slice(1, -1).replace(/\\"/g, '"').replace(/\\\\/g, '\\');
		}
		try {
			return config.json ? JSON.parse(s) : s;
		} catch(er) {}
	}

	var config = $.cookie = function (key, value, options) {

		// write
		if (value !== undefined) {
			options = $.extend({}, config.defaults, options);

			if (typeof options.expires === 'number') {
				var days = options.expires, t = options.expires = new Date();
				t.setDate(t.getDate() + days);
			}

			value = config.json ? JSON.stringify(value) : String(value);

			return (document.cookie = [
				config.raw ? key : encodeURIComponent(key),
				'=',
				config.raw ? value : encodeURIComponent(value),
				options.expires ? '; expires=' + options.expires.toUTCString() : '', // use expires attribute, max-age is not supported by IE
				options.path    ? '; path=' + options.path : '',
				options.domain  ? '; domain=' + options.domain : '',
				options.secure  ? '; secure' : ''
			].join(''));
		}

		// read
		var decode = config.raw ? raw : decoded;
		var cookies = document.cookie.split('; ');
		var result = key ? undefined : {};
		for (var i = 0, l = cookies.length; i < l; i++) {
			var parts = cookies[i].split('=');
			var name = decode(parts.shift());
			var cookie = decode(parts.join('='));

			if (key && key === name) {
				result = converted(cookie);
				break;
			}

			if (!key) {
				result[name] = converted(cookie);
			}
		}

		return result;
	};

	config.defaults = {};

	$.removeCookie = function (key, options) {
		if ($.cookie(key) !== undefined) {
			// Must not alter options, thus extending a fresh object...
			$.cookie(key, '', $.extend({}, options, { expires: -1 }));
			return true;
		}
		return false;
	};

}));
/*
 * jQuery.upload v1.0.2
 *
 * Copyright (c) 2010 lagos
 * Dual licensed under the MIT and GPL licenses.
 *
 * http://lagoscript.org
 */
(function($) {

   var uuid = 0;

   $.fn.upload = function(url, data, callback, type) {
      var $this = this,
            inputs,
            iframeName = 'jquery_upload' + ++uuid,
            iframe = $('<iframe name="' + iframeName + '" style="position:absolute;top:-9999px" />').appendTo('body'),
            form = '<form target="' + iframeName + '" method="post" action="' + url + '" enctype="multipart/form-data" />';

      if ($.isFunction(data)) {
         type = callback;
         callback = data;
         data = {};
      }

      //var checkbox = $('input:checkbox', this);
      //var checked = $('input:checked', this);
      //form = $(this).wrapAll(form).parent('form').attr('action', url);
      form = $this.wrap(form).parent('form');

      // Make sure radios and checkboxes keep original values
      // (IE resets checkd attributes when appending)
      //checkbox.removeAttr('checked');
      //checked.attr('checked', true);

      inputs = createInputs(data);
      inputs = inputs ? $(inputs).appendTo(form) : null;

      form.submit(function() {
         iframe.on('load', function() {
            var data = handleData(this, type);
            //      checked = $('input:checked', self);

            //form.remove();
            if (inputs) {
               inputs.remove();
            }
            $this.unwrap();
            //checkbox.removeAttr('checked');
            //checked.attr('checked', true);
            //if (inputs) {
            //   inputs.remove();
            //}

            //setTimeout(function() {
            iframe.remove();
            if (type === 'script') {
               $.globalEval(data);
            }
            if (callback) {
               callback.call(self, data);
            }
            //}, 0);
         });
      }).submit();

      //return this;
   };

   function createInputs(data) {
      return $.map(param(data), function(param) {
         return '<input type="hidden" name="' + param.name + '" value="' + param.value + '"/>';
      }).join('');
   }

   function param(data) {
      if ($.isArray(data)) {
         return data;
      }
      var params = [];

      function add(name, value) {
         params.push({name: name, value: value});
      }

      if (typeof data === 'object') {
         $.each(data, function(name) {
            if ($.isArray(this)) {
               $.each(this, function() {
                  add(name, this);
               });
            } else {
               add(name, $.isFunction(this) ? this() : this);
            }
         });
      } else if (typeof data === 'string') {
         $.each(data.split('&'), function() {
            var param = $.map(this.split('='), function(v) {
               return decodeURIComponent(v.replace(/\+/g, ' '));
            });

            add(param[0], param[1]);
         });
      }

      return params;
   }

   function handleData(iframe, type) {
      var data;
      //console.log($(iframe).contents());
      var contents = $(iframe).contents()[0];//.get(0);
      //console.log(contents);

      if ($.isXMLDoc(contents)) {
         return contents;
      }
      data = $(contents).find('body').html();

      switch (type) {
         case 'xml':
            data = $.parseXML(data);
            break;
         case 'json':
            data = $.parseJSON(data);
            break;
      }
      return data;
   }
   /*
    function parseXml(text) {
    if (window.DOMParser) {
    return new DOMParser().parseFromString(text, 'application/xml');
    } else {
    var xml = new ActiveXObject('Microsoft.XMLDOM');
    xml.async = false;
    xml.loadXML(text);
    return xml;
    }
    }
    */
})(jQuery);
// ----------------------------------------------------------------------------
// markItUp! Universal MarkUp Engine, JQuery plugin
// v 1.1.x
// Dual licensed under the MIT and GPL licenses.
// ----------------------------------------------------------------------------
// Copyright (C) 2007-2012 Jay Salvat
// http://markitup.jaysalvat.com/
// ----------------------------------------------------------------------------
// Permission is hereby granted, free of charge, to any person obtaining a copy
// of this software and associated documentation files (the "Software"), to deal
// in the Software without restriction, including without limitation the rights
// to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
// copies of the Software, and to permit persons to whom the Software is
// furnished to do so, subject to the following conditions:
// 
// The above copyright notice and this permission notice shall be included in
// all copies or substantial portions of the Software.
// 
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
// FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
// AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
// OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
// THE SOFTWARE.
// ----------------------------------------------------------------------------
(function($) {
	$.fn.markItUp = function(settings, extraSettings) {
		var method, params, options, ctrlKey, shiftKey, altKey; ctrlKey = shiftKey = altKey = false;

		if (typeof settings == 'string') {
			method = settings;
			params = extraSettings;
		} 

		options = {	id:						'',
					nameSpace:				'',
					root:					'',
					previewHandler:			false,
					previewInWindow:		'', // 'width=800, height=600, resizable=yes, scrollbars=yes'
					previewInElement:		'',
					previewAutoRefresh:		true,
					previewPosition:		'after',
					previewTemplatePath:	'~/templates/preview.html',
					previewParser:			false,
					previewParserPath:		'',
					previewParserVar:		'data',
					resizeHandle:			true,
					beforeInsert:			'',
					afterInsert:			'',
					onEnter:				{},
					onShiftEnter:			{},
					onCtrlEnter:			{},
					onTab:					{},
					markupSet:			[	{ /* set */ } ]
				};
		$.extend(options, settings, extraSettings);

		// compute markItUp! path
		if (!options.root) {
			$('script').each(function(a, tag) {
				miuScript = $(tag).get(0).src.match(/(.*)jquery\.markitup(\.pack)?\.js$/);
				if (miuScript !== null) {
					options.root = miuScript[1];
				}
			});
		}

		// Quick patch to keep compatibility with jQuery 1.9
		var uaMatch = function(ua) {
			ua = ua.toLowerCase();

			var match = /(chrome)[ \/]([\w.]+)/.exec(ua) ||
				/(webkit)[ \/]([\w.]+)/.exec(ua) ||
				/(opera)(?:.*version|)[ \/]([\w.]+)/.exec(ua) ||
				/(msie) ([\w.]+)/.exec(ua) ||
				ua.indexOf("compatible") < 0 && /(mozilla)(?:.*? rv:([\w.]+)|)/.exec(ua) ||
				[];

			return {
				browser: match[ 1 ] || "",
				version: match[ 2 ] || "0"
			};
		};
		var matched = uaMatch( navigator.userAgent );
		var browser = {};

		if (matched.browser) {
			browser[matched.browser] = true;
			browser.version = matched.version;
		}
		if (browser.chrome) {
			browser.webkit = true;
		} else if (browser.webkit) {
			browser.safari = true;
		}

		return this.each(function() {
			var $$, textarea, levels, scrollPosition, caretPosition, caretOffset,
				clicked, hash, header, footer, previewWindow, template, iFrame, abort;
			$$ = $(this);
			textarea = this;
			levels = [];
			abort = false;
			scrollPosition = caretPosition = 0;
			caretOffset = -1;

			options.previewParserPath = localize(options.previewParserPath);
			options.previewTemplatePath = localize(options.previewTemplatePath);

			if (method) {
				switch(method) {
					case 'remove':
						remove();
					break;
					case 'insert':
						markup(params);
					break;
					default: 
						$.error('Method ' +  method + ' does not exist on jQuery.markItUp');
				}
				return;
			}

			// apply the computed path to ~/
			function localize(data, inText) {
				if (inText) {
					return 	data.replace(/("|')~\//g, "$1"+options.root);
				}
				return 	data.replace(/^~\//, options.root);
			}

			// init and build editor
			function init() {
				id = ''; nameSpace = '';
				if (options.id) {
					id = 'id="'+options.id+'"';
				} else if ($$.attr("id")) {
					id = 'id="markItUp'+($$.attr("id").substr(0, 1).toUpperCase())+($$.attr("id").substr(1))+'"';

				}
				if (options.nameSpace) {
					nameSpace = 'class="'+options.nameSpace+'"';
				}
				$$.wrap('<div '+nameSpace+'></div>');
				$$.wrap('<div '+id+' class="markItUp"></div>');
				$$.wrap('<div class="markItUpContainer"></div>');
				$$.addClass("markItUpEditor");

				// add the header before the textarea
				header = $('<div class="markItUpHeader"></div>').insertBefore($$);
				$(dropMenus(options.markupSet)).appendTo(header);

				// add the footer after the textarea
				footer = $('<div class="markItUpFooter"></div>').insertAfter($$);

				// add the resize handle after textarea
				if (options.resizeHandle === true && browser.safari !== true) {
					resizeHandle = $('<div class="markItUpResizeHandle"></div>')
						.insertAfter($$)
						.bind("mousedown.markItUp", function(e) {
							var h = $$.height(), y = e.clientY, mouseMove, mouseUp;
							mouseMove = function(e) {
								$$.css("height", Math.max(20, e.clientY+h-y)+"px");
								return false;
							};
							mouseUp = function(e) {
								$("html").unbind("mousemove.markItUp", mouseMove).unbind("mouseup.markItUp", mouseUp);
								return false;
							};
							$("html").bind("mousemove.markItUp", mouseMove).bind("mouseup.markItUp", mouseUp);
					});
					footer.append(resizeHandle);
				}

				// listen key events
				$$.bind('keydown.markItUp', keyPressed).bind('keyup', keyPressed);
				
				// bind an event to catch external calls
				$$.bind("insertion.markItUp", function(e, settings) {
					if (settings.target !== false) {
						get();
					}
					if (textarea === $.markItUp.focused) {
						markup(settings);
					}
				});

				// remember the last focus
				$$.bind('focus.markItUp', function() {
					$.markItUp.focused = this;
				});

				if (options.previewInElement) {
					refreshPreview();
				}
			}
                  
                  // added by ikki, to strip html tags for link titles
			function stripHTML(html)
			{
			   var tmp = document.createElement("DIV");
			   tmp.innerHTML = html;
			   return tmp.textContent||tmp.innerText;
			}

			// recursively build header with dropMenus from markupset
			function dropMenus(markupSet) {
				var ul = $('<ul></ul>'), i = 0;
				$('li:hover > ul', ul).css('display', 'block');
				$.each(markupSet, function() {
					var button = this, t = '', title, li, j;
					//title = (button.key) ? (button.name||'')+' [Ctrl+'+button.key+']' : (button.name||'');
                              title = (button.key) ? (stripHTML(button.name)||'')+' [Ctrl+'+button.key+']' : (stripHTML(button.name)||'');
					key   = (button.key) ? 'accesskey="'+button.key+'"' : '';
					if (button.separator) {
						li = $('<li class="markItUpSeparator">'+(button.separator||'')+'</li>').appendTo(ul);
					} else {
						i++;
						for (j = levels.length -1; j >= 0; j--) {
							t += levels[j]+"-";
						}
						li = $('<li class="markItUpButton markItUpButton'+t+(i)+' '+(button.className||'')+'"><a href="" '+key+' title="'+title+'">'+(button.name||'')+'</a></li>')
						.bind("contextmenu.markItUp", function() { // prevent contextmenu on mac and allow ctrl+click
							return false;
						}).bind('click.markItUp', function(e) {
							e.preventDefault();
						}).bind("focusin.markItUp", function(){
                            $$.focus();
						}).bind('mouseup', function() {
							if (button.call) {
								eval(button.call)();
							}
							setTimeout(function() { markup(button) },1);
							return false;
						}).bind('mouseenter.markItUp', function() {
								$('> ul', this).show();
								$(document).one('click', function() { // close dropmenu if click outside
										$('ul ul', header).hide();
									}
								);
						}).bind('mouseleave.markItUp', function() {
								$('> ul', this).hide();
						}).appendTo(ul);
						if (button.dropMenu) {
							levels.push(i);
							$(li).addClass('markItUpDropMenu').append(dropMenus(button.dropMenu));
						}
					}
				}); 
				levels.pop();
				return ul;
			}

			// markItUp! markups
			function magicMarkups(string) {
				if (string) {
					string = string.toString();
					string = string.replace(/\(\!\(([\s\S]*?)\)\!\)/g,
						function(x, a) {
							var b = a.split('|!|');
							if (altKey === true) {
								return (b[1] !== undefined) ? b[1] : b[0];
							} else {
								return (b[1] === undefined) ? "" : b[0];
							}
						}
					);
					// [![prompt]!], [![prompt:!:value]!]
					string = string.replace(/\[\!\[([\s\S]*?)\]\!\]/g,
						function(x, a) {
							var b = a.split(':!:');
							if (abort === true) {
								return false;
							}
							value = prompt(b[0], (b[1]) ? b[1] : '');
							if (value === null) {
								abort = true;
							}
							return value;
						}
					);
					return string;
				}
				return "";
			}

			// prepare action
			function prepare(action) {
				if ($.isFunction(action)) {
					action = action(hash);
				}
				return magicMarkups(action);
			}

			// build block to insert
			function build(string) {
				var openWith 			= prepare(clicked.openWith);
				var placeHolder 		= prepare(clicked.placeHolder);
				var replaceWith 		= prepare(clicked.replaceWith);
				var closeWith 			= prepare(clicked.closeWith);
				var openBlockWith 		= prepare(clicked.openBlockWith);
				var closeBlockWith 		= prepare(clicked.closeBlockWith);
				var multiline 			= clicked.multiline;
				
				if (replaceWith !== "") {
					block = openWith + replaceWith + closeWith;
				} else if (selection === '' && placeHolder !== '') {
					block = openWith + placeHolder + closeWith;
				} else {
					string = string || selection;

					var lines = [string], blocks = [];
					
					if (multiline === true) {
						lines = string.split(/\r?\n/);
					}
					
					for (var l = 0; l < lines.length; l++) {
						line = lines[l];
						var trailingSpaces;
						if (trailingSpaces = line.match(/ *$/)) {
							blocks.push(openWith + line.replace(/ *$/g, '') + closeWith + trailingSpaces);
						} else {
							blocks.push(openWith + line + closeWith);
						}
					}
					
					block = blocks.join("\n");
				}

				block = openBlockWith + block + closeBlockWith;

				return {	block:block, 
							openBlockWith:openBlockWith,
							openWith:openWith, 
							replaceWith:replaceWith, 
							placeHolder:placeHolder,
							closeWith:closeWith,
							closeBlockWith:closeBlockWith
					};
			}

			// define markup to insert
			function markup(button) {
				var len, j, n, i;
				hash = clicked = button;
				get();
				$.extend(hash, {	line:"", 
						 			root:options.root,
									textarea:textarea, 
									selection:(selection||''), 
									caretPosition:caretPosition,
									ctrlKey:ctrlKey, 
									shiftKey:shiftKey, 
									altKey:altKey
								}
							);
				// callbacks before insertion
				prepare(options.beforeInsert);
				prepare(clicked.beforeInsert);
				if ((ctrlKey === true && shiftKey === true) || button.multiline === true) {
					prepare(clicked.beforeMultiInsert);
				}			
				$.extend(hash, { line:1 });

				if ((ctrlKey === true && shiftKey === true)) {
					lines = selection.split(/\r?\n/);
					for (j = 0, n = lines.length, i = 0; i < n; i++) {
						if ($.trim(lines[i]) !== '') {
							$.extend(hash, { line:++j, selection:lines[i] } );
							lines[i] = build(lines[i]).block;
						} else {
							lines[i] = "";
						}
					}

					string = { block:lines.join('\n')};
					start = caretPosition;
					len = string.block.length + ((browser.opera) ? n-1 : 0);
				} else if (ctrlKey === true) {
					string = build(selection);
					start = caretPosition + string.openWith.length;
					len = string.block.length - string.openWith.length - string.closeWith.length;
					len = len - (string.block.match(/ $/) ? 1 : 0);
					len -= fixIeBug(string.block);
				} else if (shiftKey === true) {
					string = build(selection);
					start = caretPosition;
					len = string.block.length;
					len -= fixIeBug(string.block);
				} else {
					string = build(selection);
					start = caretPosition + string.block.length ;
					len = 0;
					start -= fixIeBug(string.block);
				}
				if ((selection === '' && string.replaceWith === '')) {
					caretOffset += fixOperaBug(string.block);
					
					start = caretPosition + string.openBlockWith.length + string.openWith.length;
					len = string.block.length - string.openBlockWith.length - string.openWith.length - string.closeWith.length - string.closeBlockWith.length;

					caretOffset = $$.val().substring(caretPosition,  $$.val().length).length;
					caretOffset -= fixOperaBug($$.val().substring(0, caretPosition));
				}
				$.extend(hash, { caretPosition:caretPosition, scrollPosition:scrollPosition } );

				if (string.block !== selection && abort === false) {
					insert(string.block);
					set(start, len);
				} else {
					caretOffset = -1;
				}
				get();

				$.extend(hash, { line:'', selection:selection });

				// callbacks after insertion
				if ((ctrlKey === true && shiftKey === true) || button.multiline === true) {
					prepare(clicked.afterMultiInsert);
				}
				prepare(clicked.afterInsert);
				prepare(options.afterInsert);

				// refresh preview if opened
				if (previewWindow && options.previewAutoRefresh) {
					refreshPreview(); 
				}
																									
				// reinit keyevent
				shiftKey = altKey = ctrlKey = abort = false;
			}

			// Substract linefeed in Opera
			function fixOperaBug(string) {
				if (browser.opera) {
					return string.length - string.replace(/\n*/g, '').length;
				}
				return 0;
			}
			// Substract linefeed in IE
			function fixIeBug(string) {
				if (browser.msie) {
					return string.length - string.replace(/\r*/g, '').length;
				}
				return 0;
			}
				
			// add markup
			function insert(block) {	
				if (document.selection) {
					var newSelection = document.selection.createRange();
					newSelection.text = block;
				} else {
					textarea.value =  textarea.value.substring(0, caretPosition)  + block + textarea.value.substring(caretPosition + selection.length, textarea.value.length);
				}
			}

			// set a selection
			function set(start, len) {
				if (textarea.createTextRange){
					// quick fix to make it work on Opera 9.5
					if (browser.opera && browser.version >= 9.5 && len == 0) {
						return false;
					}
					range = textarea.createTextRange();
					range.collapse(true);
					range.moveStart('character', start); 
					range.moveEnd('character', len); 
					range.select();
				} else if (textarea.setSelectionRange ){
					textarea.setSelectionRange(start, start + len);
				}
				textarea.scrollTop = scrollPosition;
				textarea.focus();
			}

			// get the selection
			function get() {
				textarea.focus();

				scrollPosition = textarea.scrollTop;
				if (document.selection) {
					selection = document.selection.createRange().text;
					if (browser.msie) { // ie
						var range = document.selection.createRange(), rangeCopy = range.duplicate();
						rangeCopy.moveToElementText(textarea);
						caretPosition = -1;
						while(rangeCopy.inRange(range)) {
							rangeCopy.moveStart('character');
							caretPosition ++;
						}
					} else { // opera
						caretPosition = textarea.selectionStart;
					}
				} else { // gecko & webkit
					caretPosition = textarea.selectionStart;

					selection = textarea.value.substring(caretPosition, textarea.selectionEnd);
				} 
				return selection;
			}

			// open preview window
			function preview() {
                        return; //do nothing, just disable this function
				if (typeof options.previewHandler === 'function') {
					previewWindow = true;
				} else if (options.previewInElement) {
					previewWindow = $(options.previewInElement);
				} else if (!previewWindow || previewWindow.closed) {
					if (options.previewInWindow) {
						previewWindow = window.open('', 'preview', options.previewInWindow);
						$(window).unload(function() {
							previewWindow.close();
						});
					} else {
						iFrame = $('<iframe class="markItUpPreviewFrame"></iframe>');
						if (options.previewPosition == 'after') {
							iFrame.insertAfter(footer);
						} else {
							iFrame.insertBefore(header);
						}	
						previewWindow = iFrame[iFrame.length - 1].contentWindow || frame[iFrame.length - 1];
					}
				} else if (altKey === true) {
					if (iFrame) {
						iFrame.remove();
					} else {
						previewWindow.close();
					}
					previewWindow = iFrame = false;
				}
				if (!options.previewAutoRefresh) {
					refreshPreview(); 
				}
				if (options.previewInWindow) {
					previewWindow.focus();
				}
			}

			// refresh Preview window
			function refreshPreview() {
 				renderPreview();
			}

			function renderPreview() {
				var phtml;
				if (options.previewHandler && typeof options.previewHandler === 'function') {
					options.previewHandler( $$.val() );
				} else if (options.previewParser && typeof options.previewParser === 'function') {
					var data = options.previewParser( $$.val() );
					writeInPreview(localize(data, 1) ); 
				} else if (options.previewParserPath !== '') {
					$.ajax({
						type: 'POST',
						dataType: 'text',
						global: false,
						url: options.previewParserPath,
						data: options.previewParserVar+'='+encodeURIComponent($$.val()),
						success: function(data) {
							writeInPreview( localize(data, 1) ); 
						}
					});
				} else {
					if (!template) {
						$.ajax({
							url: options.previewTemplatePath,
							dataType: 'text',
							global: false,
							success: function(data) {
								writeInPreview( localize(data, 1).replace(/<!-- content -->/g, $$.val()) );
							}
						});
					}
				}
				return false;
			}
			
			function writeInPreview(data) {
				if (options.previewInElement) {
					$(options.previewInElement).html(data);
				} else if (previewWindow && previewWindow.document) {			
					try {
						sp = previewWindow.document.documentElement.scrollTop
					} catch(e) {
						sp = 0;
					}	
					previewWindow.document.open();
					previewWindow.document.write(data);
					previewWindow.document.close();
					previewWindow.document.documentElement.scrollTop = sp;
				}
			}
			
			// set keys pressed
			function keyPressed(e) { 
				shiftKey = e.shiftKey;
				altKey = e.altKey;
				ctrlKey = (!(e.altKey && e.ctrlKey)) ? (e.ctrlKey || e.metaKey) : false;

				if (e.type === 'keydown') {
					if (ctrlKey === true) {
						li = $('a[accesskey="'+((e.keyCode == 13) ? '\\n' : String.fromCharCode(e.keyCode))+'"]', header).parent('li');
						if (li.length !== 0) {
							ctrlKey = false;
							setTimeout(function() {
								li.triggerHandler('mouseup');
							},1);
							return false;
						}
					}
					if (e.keyCode === 13 || e.keyCode === 10) { // Enter key
						if (ctrlKey === true) {  // Enter + Ctrl
							ctrlKey = false;
							markup(options.onCtrlEnter);
							return options.onCtrlEnter.keepDefault;
						} else if (shiftKey === true) { // Enter + Shift
							shiftKey = false;
							markup(options.onShiftEnter);
							return options.onShiftEnter.keepDefault;
						} else { // only Enter
							markup(options.onEnter);
							return options.onEnter.keepDefault;
						}
					}
					if (e.keyCode === 9) { // Tab key
						if (shiftKey == true || ctrlKey == true || altKey == true) {
							return false; 
						}
						if (caretOffset !== -1) {
							get();
							caretOffset = $$.val().length - caretOffset;
							set(caretOffset, 0);
							caretOffset = -1;
							return false;
						} else {
							markup(options.onTab);
							return options.onTab.keepDefault;
						}
					}
				}
			}

			function remove() {
				$$.unbind(".markItUp").removeClass('markItUpEditor');
				$$.parent('div').parent('div.markItUp').parent('div').replaceWith($$);
				$$.data('markItUp', null);
			}

			init();
		});
	};

	$.fn.markItUpRemove = function() {
		return this.each(function() {
				$(this).markItUp('remove');
			}
		);
	};

	$.markItUp = function(settings) {
		var options = { target:false };
		$.extend(options, settings);
		if (options.target) {
			return $(options.target).each(function() {
				$(this).focus();
				$(this).trigger('insertion', [options]);
			});
		} else {
			$('textarea').trigger('insertion', [options]);
		}
	};
})(jQuery);
// ----------------------------------------------------------------------------
// markItUp!
// ----------------------------------------------------------------------------
// Copyright (C) 2008 Jay Salvat
// http://markitup.jaysalvat.com/
// ----------------------------------------------------------------------------
// BBCode tags example
// http://en.wikipedia.org/wiki/Bbcode
// ----------------------------------------------------------------------------
// Feel free to add more tags
// ----------------------------------------------------------------------------
var mySettings = {
	previewParserPath:	'', // path to your BBCode parser
	markupSet: [
		{name:'Bold', key:'B', openWith:'[b]', closeWith:'[/b]'},
		{name:'Italic', key:'I', openWith:'[i]', closeWith:'[/i]'},
		{name:'Underline', key:'U', openWith:'[u]', closeWith:'[/u]'},
		{name:'Strikethrough', openWith:'[s]', closeWith:'[/s]'},
		{name:'Font Size', openWith:'[size=[![Font size]!]]', closeWith:'[/size]',
		dropMenu :[
			{name:'<span style="line-height:16px;font-size:120%">Big</span>', openWith:'[size=120%]', closeWith:'[/size]' },
			{name:'<span style="line-height:16px;font-size:100%">Normal</span>', openWith:'[size=100%]', closeWith:'[/size]' },
			{name:'<span style="line-height:16px;font-size:80%">Small</span>', openWith:'[size=80%]', closeWith:'[/size]' }
		]},
		{	name:'Font Color', 
			className:'colors', 
			openWith:'[color=[![Font Color]!]]', 
			closeWith:'[/color]', 
				dropMenu: [
					{name:'Yellow',	openWith:'[color=yellow]', 	closeWith:'[/color]', className:'col1-1' },
					{name:'Orange',	openWith:'[color=orange]', 	closeWith:'[/color]', className:'col1-2' },
					{name:'Red', 	openWith:'[color=red]', 	closeWith:'[/color]', className:'col1-3' },
					
					{name:'Blue', 	openWith:'[color=blue]', 	closeWith:'[/color]', className:'col2-1' },
					{name:'Purple', openWith:'[color=purple]', 	closeWith:'[/color]', className:'col2-2' },
					{name:'Green', 	openWith:'[color=green]', 	closeWith:'[/color]', className:'col2-3' },
					
					{name:'White', 	openWith:'[color=white]', 	closeWith:'[/color]', className:'col3-1' },
					{name:'Gray', 	openWith:'[color=gray]', 	closeWith:'[/color]', className:'col3-2' },
					{name:'Black',	openWith:'[color=black]', 	closeWith:'[/color]', className:'col3-3' }
				]
		},
		{	name:'Font Background Color', 
			className:'bgcolors', 
			openWith:'[bgcolor=[![Font Background Color]!]]', 
			closeWith:'[/bgcolor]', 
				dropMenu: [
					{name:'Yellow',	openWith:'[bgcolor=yellow]', 	closeWith:'[/bgcolor]', className:'col1-1' },
					{name:'Orange',	openWith:'[bgcolor=orange]', 	closeWith:'[/bgcolor]', className:'col1-2' },
					{name:'Red', 	openWith:'[bgcolor=red]', 	closeWith:'[/bgcolor]', className:'col1-3' },
					
					{name:'Blue', 	openWith:'[bgcolor=blue]', 	closeWith:'[/bgcolor]', className:'col2-1' },
					{name:'Purple', openWith:'[bgcolor=purple]', 	closeWith:'[/bgcolor]', className:'col2-2' },
					{name:'Green', 	openWith:'[bgcolor=green]', 	closeWith:'[/bgcolor]', className:'col2-3' },
					
					{name:'White', 	openWith:'[bgcolor=white]', 	closeWith:'[/bgcolor]', className:'col3-1' },
					{name:'Gray', 	openWith:'[bgcolor=gray]', 	closeWith:'[/bgcolor]', className:'col3-2' },
					{name:'Black',	openWith:'[bgcolor=black]', 	closeWith:'[/bgcolor]', className:'col3-3' }
				]
		},
		{separator:'---------------' },
		{name:'Picture', key:'P', replaceWith:'[img][![Image url]!][/img]'},
		{name:'Link', key:'L', openWith:'[url=[![Link url]!]]', closeWith:'[/url]', placeHolder:'Your text to link here...'},
		{separator:'---------------' },
		{name:'Bulleted list', openWith:'[list]\n', closeWith:'\n[/list]'},
		{name:'Numeric list', openWith:'[list=[![Starting number]!]]\n', closeWith:'\n[/list]'}, 
		{name:'List item', openWith:'[*] '},
		{separator:'---------------' },
		{name:'Quotes', openWith:'[quote]', closeWith:'[/quote]'},
		{name:'Code', openWith:'[code]', closeWith:'[/code]'}, 
		{separator:'---------------' },
		{name:'Smiley', className:'smiley', call:'preview'},
		{name:'YouTube Video', className:'youtube', openWith:'[youtube]', closeWith:'[/youtube]'},
		{name:'TuDou Video', className:'tudou', openWith:'[tudou]', closeWith:'[/tudou]'}, 
		{separator:'---------------' },
		{name:'Clean', className:"clean", replaceWith:function(markitup) { return markitup.selection.replace(/\[(.*?)\]/g, "") } },
		{name:'Preview', className:"preview", call:'preview' },
		{name:'Help', className:"help", call:'preview' }
	]
};
/**
 * hoverIntent is similar to jQuery's built-in "hover" method except that
 * instead of firing the handlerIn function immediately, hoverIntent checks
 * to see if the user's mouse has slowed down (beneath the sensitivity
 * threshold) before firing the event. The handlerOut function is only
 * called after a matching handlerIn.
 *
 * hoverIntent r7 // 2013.03.11 // jQuery 1.9.1+
 * http://cherne.net/brian/resources/jquery.hoverIntent.html
 *
 * You may use hoverIntent under the terms of the MIT license. Basically that
 * means you are free to use hoverIntent as long as this header is left intact.
 * Copyright 2007, 2013 Brian Cherne
 *
 * // basic usage ... just like .hover()
 * .hoverIntent( handlerIn, handlerOut )
 * .hoverIntent( handlerInOut )
 *
 * // basic usage ... with event delegation!
 * .hoverIntent( handlerIn, handlerOut, selector )
 * .hoverIntent( handlerInOut, selector )
 *
 * // using a basic configuration object
 * .hoverIntent( config )
 *
 * @param  handlerIn   function OR configuration object
 * @param  handlerOut  function OR selector for delegation OR undefined
 * @param  selector    selector OR undefined
 * @author Brian Cherne <brian(at)cherne(dot)net>
 **/
(function($) {
    $.fn.hoverIntent = function(handlerIn,handlerOut,selector) {

        // default configuration values
        var cfg = {
            interval: 100,
            sensitivity: 7,
            timeout: 0
        };

        if ( typeof handlerIn === "object" ) {
            cfg = $.extend(cfg, handlerIn );
        } else if ($.isFunction(handlerOut)) {
            cfg = $.extend(cfg, { over: handlerIn, out: handlerOut, selector: selector } );
        } else {
            cfg = $.extend(cfg, { over: handlerIn, out: handlerIn, selector: handlerOut } );
        }

        // instantiate variables
        // cX, cY = current X and Y position of mouse, updated by mousemove event
        // pX, pY = previous X and Y position of mouse, set by mouseover and polling interval
        var cX, cY, pX, pY;

        // A private function for getting mouse position
        var track = function(ev) {
            cX = ev.pageX;
            cY = ev.pageY;
        };

        // A private function for comparing current and previous mouse position
        var compare = function(ev,ob) {
            ob.hoverIntent_t = clearTimeout(ob.hoverIntent_t);
            // compare mouse positions to see if they've crossed the threshold
            if ( ( Math.abs(pX-cX) + Math.abs(pY-cY) ) < cfg.sensitivity ) {
                $(ob).off("mousemove.hoverIntent",track);
                // set hoverIntent state to true (so mouseOut can be called)
                ob.hoverIntent_s = 1;
                return cfg.over.apply(ob,[ev]);
            } else {
                // set previous coordinates for next time
                pX = cX; pY = cY;
                // use self-calling timeout, guarantees intervals are spaced out properly (avoids JavaScript timer bugs)
                ob.hoverIntent_t = setTimeout( function(){compare(ev, ob);} , cfg.interval );
            }
        };

        // A private function for delaying the mouseOut function
        var delay = function(ev,ob) {
            ob.hoverIntent_t = clearTimeout(ob.hoverIntent_t);
            ob.hoverIntent_s = 0;
            return cfg.out.apply(ob,[ev]);
        };

        // A private function for handling mouse 'hovering'
        var handleHover = function(e) {
            // copy objects to be passed into t (required for event object to be passed in IE)
            var ev = jQuery.extend({},e);
            var ob = this;

            // cancel hoverIntent timer if it exists
            if (ob.hoverIntent_t) { ob.hoverIntent_t = clearTimeout(ob.hoverIntent_t); }

            // if e.type == "mouseenter"
            if (e.type == "mouseenter") {
                // set "previous" X and Y position based on initial entry point
                pX = ev.pageX; pY = ev.pageY;
                // update "current" X and Y position based on mousemove
                $(ob).on("mousemove.hoverIntent",track);
                // start polling interval (self-calling timeout) to compare mouse coordinates over time
                if (ob.hoverIntent_s != 1) { ob.hoverIntent_t = setTimeout( function(){compare(ev,ob);} , cfg.interval );}

                // else e.type == "mouseleave"
            } else {
                // unbind expensive mousemove event
                $(ob).off("mousemove.hoverIntent",track);
                // if hoverIntent state is true, then call the mouseOut function after the specified delay
                if (ob.hoverIntent_s == 1) { ob.hoverIntent_t = setTimeout( function(){delay(ev,ob);} , cfg.timeout );}
            }
        };

        // listen for mouseenter and mouseleave
        return this.on({'mouseenter.hoverIntent':handleHover,'mouseleave.hoverIntent':handleHover}, cfg.selector);
    };
})(jQuery);/*
 * Superfish v1.7.2 - jQuery menu widget
 * Copyright (c) 2013 Joel Birch
 *
 * Dual licensed under the MIT and GPL licenses:
 * 	http://www.opensource.org/licenses/mit-license.php
 * 	http://www.gnu.org/licenses/gpl.html
 */

;(function($) {

	var methods = (function(){
		// private properties and methods go here
		var c = {
				bcClass: 'sf-breadcrumb',
				menuClass: 'sf-js-enabled',
				anchorClass: 'sf-with-ul',
				menuArrowClass: 'sf-arrows'
			},
			ios = /iPhone|iPad|iPod/i.test(navigator.userAgent),
			wp7 = (function() {
				var style = document.documentElement.style;
				return ('behavior' in style && 'fill' in style && /iemobile/i.test(navigator.userAgent));
			})(),
			fixIos = (function(){
				if (ios) {
					// iOS clicks only bubble as far as body children
					$(window).load(function() {
						$('body').children().on('click', $.noop);
					});
				}
			})(),
			toggleMenuClasses = function($menu, o) {
				var classes = c.menuClass;
				if (o.cssArrows) {
					classes += ' ' + c.menuArrowClass;
				}
				$menu.toggleClass(classes);
			},
			setPathToCurrent = function($menu, o) {
				return $menu.find('li.' + o.pathClass).slice(0, o.pathLevels)
					.addClass(o.hoverClass + ' ' + c.bcClass)
						.filter(function() {
							return ($(this).children('ul').hide().show().length);
						}).removeClass(o.pathClass);
			},
			toggleAnchorClass = function($li) {
				$li.children('a').toggleClass(c.anchorClass);
			},
			toggleTouchAction = function($menu) {
				var touchAction = $menu.css('ms-touch-action');
				touchAction = (touchAction === 'pan-y') ? 'auto' : 'pan-y';
				$menu.css('ms-touch-action', touchAction);
			},
			applyHandlers = function($menu,o) {
				var targets = 'li:has(ul)';
				if ($.fn.hoverIntent && !o.disableHI) {
					$menu.hoverIntent(over, out, targets);
				}
				else {
					$menu
						.on('mouseenter.superfish', targets, over)
						.on('mouseleave.superfish', targets, out);
				}
				var touchevent = 'MSPointerDown.superfish';
				if (!ios) {
					touchevent += ' touchend.superfish';
				}
				if (wp7) {
					touchevent += ' mousedown.superfish';
				}
				$menu
					.on('focusin.superfish', 'li', over)
					.on('focusout.superfish', 'li', out)
					.on(touchevent, 'a', touchHandler);
			},
			touchHandler = function(e) {
				var $this = $(this),
					$ul = $this.siblings('ul');

				if ($ul.length > 0 && $ul.is(':hidden')) {
					$this.one('click.superfish', false);
					if (e.type === 'MSPointerDown') {
						$this.trigger('focus');
					} else {
						$.proxy(over, $this.parent('li'))();
					}
				}
			},
			over = function() {
				var $this = $(this),
					o = getOptions($this);
				clearTimeout(o.sfTimer);
				$this.siblings().superfish('hide').end().superfish('show');
			},
			out = function() {
				var $this = $(this),
					o = getOptions($this);
				if (ios) {
					$.proxy(close, $this, o)();
				}
				else {
					clearTimeout(o.sfTimer);
					o.sfTimer = setTimeout($.proxy(close, $this, o), o.delay);
				}
			},
			close = function(o) {
				o.retainPath = ( $.inArray(this[0], o.$path) > -1);
				this.superfish('hide');

				if (!this.parents('.' + o.hoverClass).length) {
					o.onIdle.call(getMenu(this));
					if (o.$path.length) {
						$.proxy(over, o.$path)();
					}
				}
			},
			getMenu = function($el) {
				return $el.closest('.' + c.menuClass);
			},
			getOptions = function($el) {
				return getMenu($el).data('sf-options');
			};

		return {
			// public methods
			hide: function(instant) {
				if (this.length) {
					var $this = this,
						o = getOptions($this);
						if (!o) {
							return this;
						}
					var not = (o.retainPath === true) ? o.$path : '',
						$ul = $this.find('li.' + o.hoverClass).add(this).not(not).removeClass(o.hoverClass).children('ul'),
						speed = o.speedOut;

					if (instant) {
						$ul.show();
						speed = 0;
					}
					o.retainPath = false;
					o.onBeforeHide.call($ul);
					$ul.stop(true, true).animate(o.animationOut, speed, function() {
						var $this = $(this);
						o.onHide.call($this);
					});
				}
				return this;
			},
			show: function() {
				var o = getOptions(this);
				if (!o) {
					return this;
				}
				var $this = this.addClass(o.hoverClass),
					$ul = $this.children('ul');

				o.onBeforeShow.call($ul);
				$ul.stop(true, true).animate(o.animation, o.speed, function() {
					o.onShow.call($ul);
				});
				return this;
			},
			destroy: function() {
				return this.each(function(){
					var $this = $(this),
						o = $this.data('sf-options'),
						$liHasUl = $this.find('li:has(ul)');
					if (!o) {
						return false;
					}
					clearTimeout(o.sfTimer);
					toggleMenuClasses($this, o);
					toggleAnchorClass($liHasUl);
					toggleTouchAction($this);
					// remove event handlers
					$this.off('.superfish').off('.hoverIntent');
					// clear animation's inline display style
					$liHasUl.children('ul').attr('style', function(i, style){
						return style.replace(/display[^;]+;?/g, '');
					});
					// reset 'current' path classes
					o.$path.removeClass(o.hoverClass + ' ' + c.bcClass).addClass(o.pathClass);
					$this.find('.' + o.hoverClass).removeClass(o.hoverClass);
					o.onDestroy.call($this);
					$this.removeData('sf-options');
				});
			},
			init: function(op){
				return this.each(function() {
					var $this = $(this);
					if ($this.data('sf-options')) {
						return false;
					}
					var o = $.extend({}, $.fn.superfish.defaults, op),
						$liHasUl = $this.find('li:has(ul)');
					o.$path = setPathToCurrent($this, o);

					$this.data('sf-options', o);

					toggleMenuClasses($this, o);
					toggleAnchorClass($liHasUl);
					toggleTouchAction($this);
					applyHandlers($this, o);

					$liHasUl.not('.' + c.bcClass).superfish('hide',true);

					o.onInit.call(this);
				});
			}
		};
	})();

	$.fn.superfish = function(method, args) {
		if (methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		}
		else if (typeof method === 'object' || ! method) {
			return methods.init.apply(this, arguments);
		}
		else {
			return $.error('Method ' +  method + ' does not exist on jQuery.fn.superfish');
		}
	};

	$.fn.superfish.defaults = {
		hoverClass: 'sfHover',
		pathClass: 'overrideThisToUse',
		pathLevels: 1,
		delay: 800,
		animation: {opacity:'show'},
		animationOut: {opacity:'hide'},
		speed: 'normal',
		speedOut: 'fast',
		cssArrows: true,
		disableHI: false,
		onInit: $.noop,
		onBeforeShow: $.noop,
		onShow: $.noop,
		onBeforeHide: $.noop,
		onHide: $.noop,
		onIdle: $.noop,
		onDestroy: $.noop
	};

	// soon to be deprecated
	$.fn.extend({
		hideSuperfishUl: methods.hide,
		showSuperfishUl: methods.show
	});

})(jQuery);/**
 * Coin Slider - Unique jQuery Image Slider
 * @version: 1.0 - (2010/04/04)
 * @requires jQuery v1.2.2 or later
 * @author Ivan Lazarevic
 * Examples and documentation at: http://workshop.rs/projects/coin-slider/
 
 * Licensed under MIT licence:
 *   http://www.opensource.org/licenses/mit-license.php
 **/

(function($) {

   var params = [],
         order = [],
         images = [],
         links = [],
         linksTarget = [],
         titles = [],
         interval = [],
         imagePos = [],
         appInterval = [],
         squarePos = [],
         reverse = [];

   $.fn.coinslider = $.fn.CoinSlider = function(options) {

      // squares positions
      var setFields = function(el) {

         var tWidth = parseInt(params[el.id].width / params[el.id].spw),
               sWidth = tWidth,
               tHeight = parseInt(params[el.id].height / params[el.id].sph),
               sHeight = tHeight,
               counter = 0,
               sLeft = 0,
               sTop = 0,
               i,
               j,
               tgapx = params[el.id].width - params[el.id].spw * sWidth,
               gapx = tgapx,
               tgapy = params[el.id].height - params[el.id].sph * sHeight,
               gapy = tgapy;

         for (i = 1; i <= params[el.id].sph; i++) {
            gapx = tgapx;

            if (gapy > 0) {
               gapy--;
               sHeight = tHeight + 1;
            } else {
               sHeight = tHeight;
            }

            for (j = 1; j <= params[el.id].spw; j++) {

               if (gapx > 0) {
                  gapx--;
                  sWidth = tWidth + 1;
               } else {
                  sWidth = tWidth;
               }

               order[el.id][counter] = i + "" + j;
               counter++;

               if (params[el.id].links) {
                  $('#' + el.id).append("<a href='" + links[el.id][0] + "' class='cs-" + el.id + "' id='cs-" + el.id + i + j + "' style='width:" + sWidth + "px; height:" + sHeight + "px; float: left; position: absolute;'></a>");
               } else {
                  $('#' + el.id).append("<div class='cs-" + el.id + "' id='cs-" + el.id + i + j + "' style='width:" + sWidth + "px; height:" + sHeight + "px; float: left; position: absolute;'></div>");
               }

               // positioning squares
               $("#cs-" + el.id + i + j).css({
                  //'background-position': -sLeft + 'px ' + (-sTop + 'px'),
                  'background-position': 'center center',
                  'left': sLeft,
                  'top': sTop
               });

               sLeft += sWidth;
            }

            sTop += sHeight;
            sLeft = 0;

         }

         if (!params[el.id].navigationPrevNextAlwaysShown) {

            $('.cs-' + el.id).mouseover(function() {
               $('#cs-navigation-' + el.id).show();
            });

            $('.cs-' + el.id).mouseout(function() {
               $('#cs-navigation-' + el.id).hide();
            });

            $('#cs-title-' + el.id).mouseover(function() {
               $('#cs-navigation-' + el.id).show();
            });

            $('#cs-title-' + el.id).mouseout(function() {
               $('#cs-navigation-' + el.id).hide();
            });
         }

         if (params[el.id].hoverPause) {
            $('.cs-' + el.id).mouseover(function() {
               params[el.id].pause = true;
            });

            $('.cs-' + el.id).mouseout(function() {
               params[el.id].pause = false;
            });

            $('#cs-title-' + el.id).mouseover(function() {
               params[el.id].pause = true;
            });

            $('#cs-title-' + el.id).mouseout(function() {
               params[el.id].pause = false;
            });
         }

         if (params[el.id].blurPause) {
            $(window).blur(function() {
               clearInterval(interval[el.id]);
            });

            $(window).focus(function() {
               transitionCall(el);
            });
         }

      };

      var transitionCall = function(el) {

         clearInterval(interval[el.id]);
         var delay = params[el.id].delay + params[el.id].spw * params[el.id].sph * params[el.id].sDelay;
         interval[el.id] = setInterval(function() {
            transition(el);
         }, delay);

      };

      // transitions
      var transition = function(el, direction) {

         if (params[el.id].pause === true) {
            return;
         }

         effect(el);

         squarePos[el.id] = 0;
         appInterval[el.id] = setInterval(function() {
            appereance(el, order[el.id][squarePos[el.id]]);
         }, params[el.id].sDelay);

         $(el).css({'background-image': 'url(' + images[el.id][imagePos[el.id]] + ')'});

         if (typeof(direction) == "undefined") {
            imagePos[el.id]++;
         } else {
            if (direction == 'prev') {
               imagePos[el.id]--;
            } else {
               imagePos[el.id] = direction;
            }
         }

         if (imagePos[el.id] == images[el.id].length) {
            imagePos[el.id] = 0;
         }

         if (imagePos[el.id] == -1) {
            imagePos[el.id] = images[el.id].length - 1;
         }

         $('.cs-button-' + el.id).removeClass('cs-active');
         $('#cs-button-' + el.id + "-" + (imagePos[el.id] + 1)).addClass('cs-active');

         if (titles[el.id][imagePos[el.id]]) {
            $('#cs-title-' + el.id).css({'opacity': 0}).animate({'opacity': params[el.id].opacity}, params[el.id].titleSpeed);
            $('#cs-title-' + el.id).html(titles[el.id][imagePos[el.id]]);
         } else {
            $('#cs-title-' + el.id).css('opacity', 0);
         }

      };

      var appereance = function(el, sid) {

         $('.cs-' + el.id).attr('href', links[el.id][imagePos[el.id]]).attr('target', linksTarget[el.id][imagePos[el.id]]);

         if (squarePos[el.id] == params[el.id].spw * params[el.id].sph) {
            clearInterval(appInterval[el.id]);
            return;
         }

         $('#cs-' + el.id + sid).css({opacity: 0, 'background-image': 'url(' + images[el.id][imagePos[el.id]] + ')'});
         $('#cs-' + el.id + sid).animate({opacity: 1}, 300);
         squarePos[el.id]++;

      };

      // navigation
      var setNavigation = function(el) {
         // create prev and next
         if (params[el.id].showNavigationPrevNext) {
            $(el).append("<div id='cs-navigation-" + el.id + "'></div>");

            if (!params[el.id].navigationPrevNextAlwaysShown) {
               $('#cs-navigation-' + el.id).hide();
            }

            $('#cs-navigation-' + el.id).append("<a href='#' id='cs-prev-" + el.id + "' class='cs-prev'>" + params[el.id].prevText + "</a>");
            $('#cs-navigation-' + el.id).append("<a href='#' id='cs-next-" + el.id + "' class='cs-next'>" + params[el.id].nextText + "</a>");
            $('#cs-prev-' + el.id).css({
               'position': 'absolute',
               'top': params[el.id].height / 2 - 15,
               'left': 0,
               'z-index': 1001,
               'line-height': '30px',
               'opacity': params[el.id].opacity
            }).click(function(e) {
               e.preventDefault();
               transition(el, 'prev');
               transitionCall(el);
            }).mouseover(function() {
               $('#cs-navigation-' + el.id).show();
            });

            $('#cs-next-' + el.id).css({
               'position': 'absolute',
               'top': params[el.id].height / 2 - 15,
               'right': 0,
               'z-index': 1001,
               'line-height': '30px',
               'opacity': params[el.id].opacity
            }).click(function(e) {
               e.preventDefault();
               transition(el);
               transitionCall(el);
            }).mouseover(function() {
               $('#cs-navigation-' + el.id).show();
            });

            $('#cs-navigation-' + el.id + ' a').mouseout(function() {
               if (!params[el.id].navigationPrevNextAlwaysShown)
                  $('#cs-navigation-' + el.id).hide();

               params[el.id].pause = false;
            });
         }

         // image buttons
         if (params[el.id].showNavigationButtons) {
            //$("<div id='cs-buttons-" + el.id + "' class='cs-buttons'></div>").appendTo($('#coin-slider-' + el.id));
            $(el).append("<div id='cs-buttons-" + el.id + "' class='cs-buttons'></div>");


            var k;
            for (k = 1; k < images[el.id].length + 1; k++) {
               $('#cs-buttons-' + el.id).append("<a href='#' class='cs-button-" + el.id + "' id='cs-button-" + el.id + "-" + k + "'>" + k + "</a>");
            }

            $.each($('.cs-button-' + el.id), function(i, item) {
               $(item).click(function(e) {
                  $('.cs-button-' + el.id).removeClass('cs-active');
                  $(this).addClass('cs-active');
                  e.preventDefault();
                  transition(el, i);
                  transitionCall(el);
               });
            });

            $("#cs-buttons-" + el.id).css({
               'left': '50%',
               'margin-left': -images[el.id].length * 15 / 2 - 5,
               'position': 'absolute',
               'top': '5px',
               'z-index': 1001

            });
         }

      };

      // effects
      var effect = function(el) {
         var effA = ['random', 'swirl', 'rain', 'straight'],
               i,
               j,
               counter,
               eff;

         if (params[el.id].effect === '') {
            eff = effA[Math.floor(Math.random() * (effA.length))];
         } else {
            eff = params[el.id].effect;
         }

         order[el.id] = [];

         if (eff == 'random') {
            counter = 0;
            for (i = 1; i <= params[el.id].sph; i++) {
               for (j = 1; j <= params[el.id].spw; j++) {
                  order[el.id][counter] = i + "" + j;
                  counter++;
               }
            }
            randomEffect(order[el.id]);
         }

         if (eff == 'rain') {
            rain(el);
         }

         if (eff == 'swirl') {
            swirl(el);
         }

         if (eff == 'straight') {
            straight(el);
         }

         reverse[el.id] *= -1;

         if (reverse[el.id] > 0) {
            order[el.id].reverse();
         }

      };

      // shuffle array function
      var randomEffect = function(arr) {

         var i = arr.length,
               j,
               tempi,
               tempj;

         if (i === 0) {
            return false;
         }

         while (--i) {
            j = Math.floor(Math.random() * (i + 1));
            tempi = arr[i];
            tempj = arr[j];
            arr[i] = tempj;
            arr[j] = tempi;
         }
      };

      //swirl effect by milos popovic
      var swirl = function(el) {

         var n = params[el.id].sph,
               m = params[el.id].spw,
               x = 1,
               y = 1,
               going = 0,
               num = 0,
               c = 0,
               check,
               dowhile = true,
               i;

         while (dowhile) {

            num = (going === 0 || going === 2) ? m : n;

            for (i = 1; i <= num; i++) {

               order[el.id][c] = x + "" + y;
               c++;

               if (i != num) {
                  switch (going) {
                     case 0 :
                        y++;
                        break;
                     case 1 :
                        x++;
                        break;
                     case 2 :
                        y--;
                        break;
                     case 3 :
                        x--;
                        break;

                  }
               }
            }

            going = (going + 1) % 4;

            switch (going) {
               case 0 :
                  m--;
                  y++;
                  break;
               case 1 :
                  n--;
                  x++;
                  break;
               case 2 :
                  m--;
                  y--;
                  break;
               case 3 :
                  n--;
                  x--;
                  break;
            }

            check = max(n, m) - min(n, m);
            if (m <= check && n <= check) {
               dowhile = false;
            }

         }
      };

      // rain effect
      var rain = function(el) {

         var n = params[el.id].sph,
               m = params[el.id].spw,
               c = 0,
               to = 1,
               to2 = 1,
               from = 1,
               dowhile = true;

         while (dowhile) {

            for (i = from; i <= to; i++) {
               order[el.id][c] = i + '' + parseInt(to2 - i + 1);
               c++;
            }

            to2++;

            if (to < n && to2 < m && n < m) {
               to++;
            }

            if (to < n && n >= m) {
               to++;
            }

            if (to2 > m) {
               from++;
            }

            if (from > to) {
               dowhile = false;
            }

         }

      };

      // straight effect
      var straight = function(el) {
         var counter = 0,
               i,
               j;

         for (i = 1; i <= params[el.id].sph; i++) {
            for (j = 1; j <= params[el.id].spw; j++) {
               order[el.id][counter] = i + '' + j;
               counter++;
            }
         }
      };

      var min = function(n, m) {
         if (n > m) {
            return m;
         } else {
            return n;
         }
      };

      var max = function(n, m) {
         if (n < m) {
            return m;
         } else {
            return n;
         }
      };

      var init = function(el) {

         order[el.id] = [];	// order of square appereance
         images[el.id] = [];
         links[el.id] = [];
         linksTarget[el.id] = [];
         titles[el.id] = [];
         imagePos[el.id] = 0;
         squarePos[el.id] = 0;
         reverse[el.id] = 1;

         params[el.id] = $.extend({}, $.fn.coinslider.defaults, options);

         // create images, links and titles arrays
         $.each($('#' + el.id + ' img'), function(i, item) {
            images[el.id][i] = $(item).attr('data-src');
            links[el.id][i] = $(item).parent().is('a') ? $(item).parent().attr('href') : '';
            linksTarget[el.id][i] = $(item).parent().is('a') ? $(item).parent().attr('target') : '';
            //titles[el.id][i] = $(item).next().is('span') ? $(item).next().html() : '';
            titles[el.id][i] = $(item).attr('alt');
            $(item).hide();
            //$(item).next().hide();
         });

         // set panel
         $(el).css({
            'background-image': 'url(' + images[el.id][0] + ')',
            'width': params[el.id].width,
            'height': params[el.id].height,
            'position': 'relative',
            'background-position': 'center center'
         }).wrap("<div class='coin-slider' id='coin-slider-" + el.id + "' />");

         // create title bar
         $('#' + el.id).append("<div class='cs-title' id='cs-title-" + el.id + "' style='position: absolute; bottom:0; left: 0; z-index: 1000;'></div>");

         setFields(el);

         if (params[el.id].navigation) {
            setNavigation(el);
         }

         transition(el, 0);
         transitionCall(el);

      };

      this.each(
            function() {
               init(this);
            }
      );
   };

   // default values
   $.fn.coinslider.defaults = {
      width: 600, // width of slider panel
      height: 300, // height of slider panel
      spw: 1, // squares per width
      sph: 1, // squares per height
      delay: 5000, // delay between images in ms
      sDelay: 50, // delay beetwen squares in ms
      opacity: 0.7, // opacity of title and navigation
      titleSpeed: 1000, // speed of title appereance in ms
      effect: 'straight', // random, swirl, rain, straight
      links: true, // show images as links
      hoverPause: true, // pause on hover
      blurPause: true, // pause on window blur;
      prevText: '<',
      nextText: '>',
      navigation: true, // show/hide prev, next and buttons
      showNavigationPrevNext: true,
      showNavigationButtons: true,
      navigationPrevNextAlwaysShown: false
   };

})(jQuery);/*
 * Metadata - jQuery plugin for parsing metadata from elements
 *
 * Copyright (c) 2006 John Resig, Yehuda Katz, J�rn Zaefferer, Paul McLanahan
 *
	* Licensed under http://en.wikipedia.org/wiki/MIT_License
 *
 *
 */

/**
 * Sets the type of metadata to use. Metadata is encoded in JSON, and each property
 * in the JSON will become a property of the element itself.
 *
 * There are three supported types of metadata storage:
 *
 *   attr:  Inside an attribute. The name parameter indicates *which* attribute.
 *          
 *   class: Inside the class attribute, wrapped in curly braces: { }
 *   
 *   elem:  Inside a child element (e.g. a script tag). The
 *          name parameter indicates *which* element.
 *          
 * The metadata for an element is loaded the first time the element is accessed via jQuery.
 *
 * As a result, you can define the metadata type, use $(expr) to load the metadata into the elements
 * matched by expr, then redefine the metadata type and run another $(expr) for other elements.
 * 
 * @name $.metadata.setType
 *
 * @example <p id="one" class="some_class {item_id: 1, item_label: 'Label'}">This is a p</p>
 * @before $.metadata.setType("class")
 * @after $("#one").metadata().item_id == 1; $("#one").metadata().item_label == "Label"
 * @desc Reads metadata from the class attribute
 * 
 * @example <p id="one" class="some_class" data="{item_id: 1, item_label: 'Label'}">This is a p</p>
 * @before $.metadata.setType("attr", "data")
 * @after $("#one").metadata().item_id == 1; $("#one").metadata().item_label == "Label"
 * @desc Reads metadata from a "data" attribute
 * 
 * @example <p id="one" class="some_class"><script>{item_id: 1, item_label: 'Label'}</script>This is a p</p>
 * @before $.metadata.setType("elem", "script")
 * @after $("#one").metadata().item_id == 1; $("#one").metadata().item_label == "Label"
 * @desc Reads metadata from a nested script element
 * 
 * @param String type The encoding type
 * @param String name The name of the attribute to be used to get metadata (optional)
 * @cat Plugins/Metadata
 * @descr Sets the type of encoding to be used when loading metadata for the first time
 * @type undefined
 * @see metadata()
 */

(function($) {

$.extend({
	metadata : {
		defaults : {
			type: 'class',
			name: 'metadata',
			cre: /({.*})/,
			single: 'metadata'
		},
		setType: function( type, name ){
			this.defaults.type = type;
			this.defaults.name = name;
		},
		get: function( elem, opts ){
			var settings = $.extend({},this.defaults,opts);
			// check for empty string in single property
			if ( !settings.single.length ) settings.single = 'metadata';
			
			var data = $.data(elem, settings.single);
			// returned cached data if it already exists
			if ( data ) return data;
			
			data = "{}";
			
			if ( settings.type == "class" ) {
				var m = settings.cre.exec( elem.className );
				if ( m )
					data = m[1];
			} else if ( settings.type == "elem" ) {
				if( !elem.getElementsByTagName ) return;
				var e = elem.getElementsByTagName(settings.name);
				if ( e.length )
					data = $.trim(e[0].innerHTML);
			} else if ( elem.getAttribute != undefined ) {
				var attr = elem.getAttribute( settings.name );
				if ( attr )
					data = attr;
			}
			
			if ( data.indexOf( '{' ) <0 )
			data = "{" + data + "}";
			
			data = eval("(" + data + ")");
			
			$.data( elem, settings.single, data );
			return data;
		}
	}
});

/**
 * Returns the metadata object for the first member of the jQuery object.
 *
 * @name metadata
 * @descr Returns element's metadata object
 * @param Object opts An object contianing settings to override the defaults
 * @type jQuery
 * @cat Plugins/Metadata
 */
$.fn.metadata = function( opts ){
	return $.metadata.get( this[0], opts );
};

})(jQuery);/*
 ### jQuery Star Rating Plugin v4.11 - 2013-03-14 ###
 * Home: http://www.fyneworks.com/jquery/star-rating/
 * Code: http://code.google.com/p/jquery-star-rating-plugin/
 *
	* Licensed under http://en.wikipedia.org/wiki/MIT_License
 ###
*/

/*# AVOID COLLISIONS #*/
;if(window.jQuery) (function($){
/*# AVOID COLLISIONS #*/
	
	// IE6 Background Image Fix
	if ((!$.support.opacity && !$.support.style)) try { document.execCommand("BackgroundImageCache", false, true)} catch(e) { };
	// Thanks to http://www.visualjquery.com/rating/rating_redux.html
	
	// plugin initialization
	$.fn.rating = function(options){
		if(this.length==0) return this; // quick fail
		
		// Handle API methods
		if(typeof arguments[0]=='string'){
			// Perform API methods on individual elements
			if(this.length>1){
				var args = arguments;
				return this.each(function(){
					$.fn.rating.apply($(this), args);
    });
			};
			// Invoke API method handler
			$.fn.rating[arguments[0]].apply(this, $.makeArray(arguments).slice(1) || []);
			// Quick exit...
			return this;
		};
		
		// Initialize options for this call
		var options = $.extend(
			{}/* new object */,
			$.fn.rating.options/* default options */,
			options || {} /* just-in-time options */
		);
		
		// Allow multiple controls with the same name by making each call unique
		$.fn.rating.calls++;
		
		// loop through each matched element
		this
		 .not('.star-rating-applied')
			.addClass('star-rating-applied')
		.each(function(){
			
			// Load control parameters / find context / etc
			var control, input = $(this);
			var eid = (this.name || 'unnamed-rating').replace(/\[|\]/g, '_').replace(/^\_+|\_+$/g,'');
			var context = $(this.form || document.body);
			
			// FIX: http://code.google.com/p/jquery-star-rating-plugin/issues/detail?id=23
			var raters = context.data('rating');
			if(!raters || raters.call!=$.fn.rating.calls) raters = { count:0, call:$.fn.rating.calls };
			var rater = raters[eid] || context.data('rating'+eid);
			
			// if rater is available, verify that the control still exists
			if(rater) control = rater.data('rating');
			
			if(rater && control)//{// save a byte!
				// add star to control if rater is available and the same control still exists
				control.count++;
				
			//}// save a byte!
			else{
				// create new control if first star or control element was removed/replaced
				
				// Initialize options for this rater
				control = $.extend(
					{}/* new object */,
					options || {} /* current call options */,
					($.metadata? input.metadata(): ($.meta?input.data():null)) || {}, /* metadata options */
					{ count:0, stars: [], inputs: [] }
				);
				
				// increment number of rating controls
				control.serial = raters.count++;
				
				// create rating element
				rater = $('<span class="star-rating-control"/>');
				input.before(rater);
				
				// Mark element for initialization (once all stars are ready)
				rater.addClass('rating-to-be-drawn');
				
				// Accept readOnly setting from 'disabled' property
				if(input.attr('disabled') || input.hasClass('disabled')) control.readOnly = true;
				
				// Accept required setting from class property (class='required')
				if(input.hasClass('required')) control.required = true;
				
				// Create 'cancel' button
				rater.append(
					control.cancel = $('<div class="rating-cancel"><a title="' + control.cancel + '">' + control.cancelValue + '</a></div>')
					.on('mouseover',function(){
						$(this).rating('drain');
						$(this).addClass('star-rating-hover');
						//$(this).rating('focus');
					})
					.on('mouseout',function(){
						$(this).rating('draw');
						$(this).removeClass('star-rating-hover');
						//$(this).rating('blur');
					})
					.on('click',function(){
					 $(this).rating('select');
					})
					.data('rating', control)
				);
				
			}; // first element of group
			
			// insert rating star (thanks Jan Fanslau rev125 for blind support https://code.google.com/p/jquery-star-rating-plugin/issues/detail?id=125)
			var star = $('<div role="text" aria-label="'+ this.title +'" class="star-rating rater-'+ control.serial +'"><a title="' + (this.title || this.value) + '">' + this.value + '</a></div>');
			rater.append(star);
			
			// inherit attributes from input element
			if(this.id) star.attr('id', this.id);
			if(this.className) star.addClass(this.className);
			
			// Half-stars?
			if(control.half) control.split = 2;
			
			// Prepare division control
			if(typeof control.split=='number' && control.split>0){
				var stw = ($.fn.width ? star.width() : 0) || control.starWidth;
				var spi = (control.count % control.split), spw = Math.floor(stw/control.split);
				star
				// restrict star's width and hide overflow (already in CSS)
				.width(spw)
				// move the star left by using a negative margin
				// this is work-around to IE's stupid box model (position:relative doesn't work)
				.find('a').css({ 'margin-left':'-'+ (spi*spw) +'px' })
			};
			
			// readOnly?
			if(control.readOnly)//{ //save a byte!
				// Mark star as readOnly so user can customize display
				star.addClass('star-rating-readonly');
			//}  //save a byte!
			else//{ //save a byte!
			 // Enable hover css effects
				star.addClass('star-rating-live')
				 // Attach mouse events
					.on('mouseover',function(){
						$(this).rating('fill');
						$(this).rating('focus');
					})
					.on('mouseout',function(){
						$(this).rating('draw');
						$(this).rating('blur');
					})
					.on('click',function(){
						$(this).rating('select');
					})
				;
			//}; //save a byte!
			
			// set current selection
			if(this.checked)	control.current = star;
			
			// set current select for links
			if(this.nodeName=="A"){
    if($(this).hasClass('selected'))
     control.current = star;
   };
			
			// hide input element
			input.hide();
			
			// backward compatibility, form element to plugin
			input.on('change.rating',function(event){
				if(event.selfTriggered) return false;
    $(this).rating('select');
   });
			
			// attach reference to star to input element and vice-versa
			star.data('rating.input', input.data('rating.star', star));
			
			// store control information in form (or body when form not available)
			control.stars[control.stars.length] = star[0];
			control.inputs[control.inputs.length] = input[0];
			control.rater = raters[eid] = rater;
			control.context = context;
			
			input.data('rating', control);
			rater.data('rating', control);
			star.data('rating', control);
			context.data('rating', raters);
			context.data('rating'+eid, rater); // required for ajax forms
  }); // each element
		
		// Initialize ratings (first draw)
		$('.rating-to-be-drawn').rating('draw').removeClass('rating-to-be-drawn');
		
		return this; // don't break the chain...
	};
	
	/*--------------------------------------------------------*/
	
	/*
		### Core functionality and API ###
	*/
	$.extend($.fn.rating, {
		// Used to append a unique serial number to internal control ID
		// each time the plugin is invoked so same name controls can co-exist
		calls: 0,
		
		focus: function(){
			var control = this.data('rating'); if(!control) return this;
			if(!control.focus) return this; // quick fail if not required
			// find data for event
			var input = $(this).data('rating.input') || $( this.tagName=='INPUT' ? this : null );
   // focus handler, as requested by focusdigital.co.uk
			if(control.focus) control.focus.apply(input[0], [input.val(), $('a', input.data('rating.star'))[0]]);
		}, // $.fn.rating.focus
		
		blur: function(){
			var control = this.data('rating'); if(!control) return this;
			if(!control.blur) return this; // quick fail if not required
			// find data for event
			var input = $(this).data('rating.input') || $( this.tagName=='INPUT' ? this : null );
   // blur handler, as requested by focusdigital.co.uk
			if(control.blur) control.blur.apply(input[0], [input.val(), $('a', input.data('rating.star'))[0]]);
		}, // $.fn.rating.blur
		
		fill: function(){ // fill to the current mouse position.
			var control = this.data('rating'); if(!control) return this;
			// do not execute when control is in read-only mode
			if(control.readOnly) return;
			// Reset all stars and highlight them up to this element
			this.rating('drain');
			this.prevAll().addBack().filter('.rater-'+ control.serial).addClass('star-rating-hover');
		},// $.fn.rating.fill
		
		drain: function() { // drain all the stars.
			var control = this.data('rating'); if(!control) return this;
			// do not execute when control is in read-only mode
			if(control.readOnly) return;
			// Reset all stars
			control.rater.children().filter('.rater-'+ control.serial).removeClass('star-rating-on').removeClass('star-rating-hover');
		},// $.fn.rating.drain
		
		draw: function(){ // set value and stars to reflect current selection
			var control = this.data('rating'); if(!control) return this;
			// Clear all stars
			this.rating('drain');
			// Set control value
			var current = $( control.current );//? control.current.data('rating.input') : null );
			var starson = current.length ? current.prevAll().addBack().filter('.rater-'+ control.serial) : null;
			if(starson)	starson.addClass('star-rating-on');
			// Show/hide 'cancel' button
			control.cancel[control.readOnly || control.required?'hide':'show']();
			// Add/remove read-only classes to remove hand pointer
			this.siblings()[control.readOnly?'addClass':'removeClass']('star-rating-readonly');
		},// $.fn.rating.draw
		
		
		
		
		
		select: function(value,wantCallBack){ // select a value
			var control = this.data('rating'); if(!control) return this;
			// do not execute when control is in read-only mode
			if(control.readOnly) return;
			// clear selection
			control.current = null;
			// programmatically (based on user input)
			if(typeof value!='undefined' || this.length>1){
			 // select by index (0 based)
				if(typeof value=='number')
 			 return $(control.stars[value]).rating('select',undefined,wantCallBack);
				// select by literal value (must be passed as a string
				if(typeof value=='string'){
					//return
					$.each(control.stars, function(){
 					//console.log($(this).data('rating.input'), $(this).data('rating.input').val(), value, $(this).data('rating.input').val()==value?'BINGO!':'');
						if($(this).data('rating.input').val()==value) $(this).rating('select',undefined,wantCallBack);
					});
					// don't break the chain
  			return this;
				};
			}
			else{
				control.current = this[0].tagName=='INPUT' ?
				 this.data('rating.star') :
					(this.is('.rater-'+ control.serial) ? this : null);
			};
			// Update rating control state
			this.data('rating', control);
			// Update display
			this.rating('draw');
			// find current input and its sibblings
			var current = $( control.current ? control.current.data('rating.input') : null );
			var lastipt = $( control.inputs ).filter(':checked');
			var deadipt = $( control.inputs ).not(current);
			// check and uncheck elements as required
			deadipt.prop('checked',false);//.removeAttr('checked');
			current.prop('checked',true);//.attr('checked','checked');
			// trigger change on current or last selected input
			$(current.length? current : lastipt ).trigger({ type:'change', selfTriggered:true });
			// click callback, as requested here: http://plugins.jquery.com/node/1655
			if((wantCallBack || wantCallBack == undefined) && control.callback) control.callback.apply(current[0], [current.val(), $('a', control.current)[0]]);// callback event
			// don't break the chain
			return this;
  },// $.fn.rating.select
		
		
		
		
		
		readOnly: function(toggle, disable){ // make the control read-only (still submits value)
			var control = this.data('rating'); if(!control) return this;
			// setread-only status
			control.readOnly = toggle || toggle==undefined ? true : false;
			// enable/disable control value submission
			if(disable) $(control.inputs).attr("disabled", "disabled");
			else     			$(control.inputs).removeAttr("disabled");
			// Update rating control state
			this.data('rating', control);
			// Update display
			this.rating('draw');
		},// $.fn.rating.readOnly
		
		disable: function(){ // make read-only and never submit value
			this.rating('readOnly', true, true);
		},// $.fn.rating.disable
		
		enable: function(){ // make read/write and submit value
			this.rating('readOnly', false, false);
		}// $.fn.rating.select
		
 });
	
	/*--------------------------------------------------------*/
	
	/*
		### Default Settings ###
		eg.: You can override default control like this:
		$.fn.rating.options.cancel = 'Clear';
	*/
	$.fn.rating.options = { //$.extend($.fn.rating, { options: {
			cancel: 'Cancel Rating',   // advisory title for the 'cancel' link
			cancelValue: '',           // value to submit when user click the 'cancel' link
			split: 0,                  // split the star into how many parts?
			
			// Width of star image in case the plugin can't work it out. This can happen if
			// the jQuery.dimensions plugin is not available OR the image is hidden at installation
			starWidth: 16//,
			
			//NB.: These don't need to be pre-defined (can be undefined/null) so let's save some code!
			//half:     false,         // just a shortcut to control.split = 2
			//required: false,         // disables the 'cancel' button so user can only select one of the specified values
			//readOnly: false,         // disable rating plugin interaction/ values cannot be.one('change',		//focus:    function(){},  // executed when stars are focused
			//blur:     function(){},  // executed when stars are focused
			//callback: function(){},  // executed when a star is clicked
 }; //} });
	
	/*--------------------------------------------------------*/
	
	
	  // auto-initialize plugin
				$(function(){
				 $('input[type=radio].star').rating();
				});
	
	
/*# AVOID COLLISIONS #*/
})(jQuery);
/*# AVOID COLLISIONS #*/
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
var UMODE_PC = 'pc',
    UMODE_MOBILE = 'mobile',
    UROLE_GUEST = 'guest',
    UROLE_USER = 'user', // user, user<uid>
    UROLE_ADM = 'adm'; // adm, adm<aid>

$.cookie.defaults = {
   expires: 365,
   path: '/',
   domain: document.domain.split('.').slice(-2).join('.')
};

if ($.cookie('umode') == UMODE_MOBILE)
{
   document.write('<meta name="viewport" content="width=device-width, initial-scale=1.0" \/>');
}

$(document).ready(function() {

   var urole = $.cookie('urole'),
       umode = $.cookie('umode'),
       uid = $.cookie('uid'),
       ua = (window && window.navigator && window.navigator.userAgent) ? window.navigator.userAgent : '';

   if (uid == 0 && urole !== UROLE_GUEST)
   {
      urole = UROLE_GUEST;
   }

   // urole: guest, user, adm, user<uid>, adm<aid>
   if (uid > 0)
   {
      $('[data-urole="' + UROLE_GUEST + '"]').remove();
      $('[data-urole="' + UROLE_USER + '"]').show();
      $('[data-urole$="' + UROLE_USER + uid + '"]').show();
      //$('.' + UROLE_GUEST).remove();
      //$('.' + UROLE_USER).show();
      //$('.' + UROLE_USER + uid).show();
      if (urole.substring(0, 3) === UROLE_ADM)
      {
         if (urole === UROLE_ADM)
         {
            $('[data-urole^="' + UROLE_ADM + '"]').show();
            //$('.' + UROLE_ADM).show();
         }
         else
         {
            $('[data-urole^="' + urole + '"]').show();
            //$('.' + urole).show();
         }
      }
   }
   else
   {
      $('[data-urole^="' + UROLE_ADM + '"]').remove();
      $('[data-urole^="' + UROLE_USER + '"]').remove();
      $('[data-urole="' + UROLE_GUEST + '"]').show();
      //$('.' + UROLE_ADM).remove();
      //$('.' + UROLE_USER).remove();
      //$('.' + UROLE_GUEST).show();
   }

   if (umode !== UMODE_PC && umode !== UMODE_MOBILE)
   {
      umode = (ua.search(/(iPhone|Android|BlackBerry)/) >= 0 ? UMODE_MOBILE : UMODE_PC);
   }

   if (umode === UMODE_PC)
   {
      //$('head > meta[name="viewport"]').remove();
      $('[data-umode="' + UMODE_MOBILE + '"]').remove();
      $('[data-umode="' + UMODE_PC + '"]').show();
      //$('.' + UMODE_MOBILE).remove();
      //$('.' + UMODE_PC).show()
      $("ul#navbar-menu").superfish();
      $('#coin-slider').coinslider();
      $('.forum-post-wrapper').each(function() {
         var panelDiv = $('.forum-post-panel', this).first();
         var mainDiv = $('.forum-post-main', this).first();
         var height = panelDiv.outerHeight() - mainDiv.outerHeight();
         if (height > 0)
         {
            $('.post-content', mainDiv).css('padding-bottom', '+=' + height);
         }
      });
   }
   else
   {
      //$('head').append('<meta name="viewport" content="width=device-width, initial-scale=1.0" />');
      $('[data-umode="' + UMODE_PC + '"]').remove();
      $('[data-umode="' + UMODE_MOBILE + '"]').show();
      //$('.' + UMODE_PC).remove();
      //$('.' + UMODE_MOBILE).show();
      $('div#navbar_user').css('float', 'none');
      var navbar = $('ul#navbar-menu').detach().appendTo('div#page_navbar_mobile').removeClass().css('list-style-type', 'none');
      navbar.find('ul').show().css('list-style-type', 'none');
      navbar.find('a.sf-with-ul').removeClass();
      $('#coin-slider').remove();
      $('div#page_header').css('width', '100%');
      $('div#page_body').css('width', '100%');
      $('div#page_footer').css('width', '100%');
      $('div.item-list').css('width', '100%').css("float", "none");
      //$('div.forum-post-panel').remove();
      $('div.forum-post-main').css('width', '100%').css('margin-left', '0').css('clear', 'both');
   }

   $('.js_even_odd_parent').each(function() {
      var c = $(this).children();
      c.filter(':even').addClass('even');
      c.filter(':odd').addClass('odd');
   });

   if (urole !== UROLE_GUEST)
   {
      var BBEditor = $('#BBCodeEditor');
      var titleEditor = $('#edit-title');
      var editorDiv = $('#editor-div');
      var editorForm = $('#editor-form');
      var fileTable = $('#ajax-file-list');
      var fileTableBody = $('tbody', fileTable);
      var TextEditor = $('#TextEditor');

      var pmCount = $.cookie('pmCount');
      if (pmCount > 0)
      {
         $("a#pm").append('<span style="color:red;"> (' + pmCount + ') <span>');
      }

      BBEditor.markItUp(mySettings);

      $('a.bb-quote').click(function(e) {
         e.preventDefault();
         titleEditor.hide();
         editorForm.attr('action', $(this).attr('href'));
         fileTable.hide();
         fileTableBody.children().remove();
         BBEditor.val('');

         window.scrollTo(0, editorDiv.offset().top);
         BBEditor.focus();

         var data = $('#' + $(this).attr('id').replace('quote', 'raw'));
         var author = data.find('span.username').html();
         var quoteText = '[quote="' + author + '"]' + data.find('pre.postbody').html() + '[/quote]\n';
         $.markItUp({
            replaceWith: quoteText
         });
      });

      $('a.bb-reply').click(function(e) {
         e.preventDefault();
         titleEditor.hide();
         editorForm.attr('action', $(this).attr('href'));
         fileTable.hide();
         fileTableBody.children().remove();
         BBEditor.val('');

         window.scrollTo(0, editorDiv.offset().top);
         BBEditor.focus();
      });

      $('a.bb-edit').click(function(e) {
         e.preventDefault();
         var id = $(this).attr('id');
         if (id.substr(0, id.indexOf('-')) === 'node')
         {
            $('input', titleEditor).val($('#node-title').html());
            titleEditor.show();
         }
         else
         {
            titleEditor.hide();
         }
         editorForm.attr('action', $(this).attr('href'));
         BBEditor.val('');

         window.scrollTo(0, editorDiv.offset().top);
         BBEditor.focus();

         var data = $('#' + id.replace('edit', 'raw'));
         var raw = data.find('pre.postbody').html();
         $.markItUp({
            replaceWith: raw
         });

         var files = $.parseJSON(data.find('span.files').html()); // may return null
         //console.log(files);

         if (files instanceof Array && files.length > 0)
         {
            //fileTableBody.children().remove();
            fileTable.show();
            for (var i = 0; i < files.length; i++) {
               var fid = files[i].fid;
               var path = files[i].path;
               var imageExt = new Array('jpeg', 'gif', 'png');
               var fileExt = path.split('.').pop();
               var bbcode;

               if (imageExt.indexOf(fileExt) >= 0) {
                  bbcode = '[img]' + path + '[/img]';
               }
               else {
                  bbcode = '[file="' + path + '"]' + files[i].name + '[/file]';
               }

               var row = '<tr id="editfile-' + fid + '">' +
                   '<td><input type="text" maxlength="30" name="files[' + fid + '][name]" id="editfile-' + fid + '-name" size="30" value="' + files[i].name + '" class="form-text"></td>' +
                   '<td style="padding: 0 10px;">' + bbcode + '<input type="text" style="display:none;" name="files[' + fid + '][path]" value="' + path + '"></td>' +
                   '<td style="text-align: center;"><a href="/file/delete?id=' + fid + '" class="ajax-file-delete" id="editfile-' + fid + '-delete">X</a></td>' +
                   '</tr>';
               fileTableBody.append(row);
            }
         }
         else
         {
            fileTable.hide();
         }

      });

      $('a.bb-create-node').click(function(e) {
         e.preventDefault();
         editorDiv.show();
         titleEditor.show();
         $('input', titleEditor).val('').focus();
         editorForm.attr('action', $(this).attr('href'));
         fileTable.hide();
         fileTableBody.children().remove();
         BBEditor.val('');

         window.scrollTo(0, editorDiv.offset().top);
         //titleEditor.focus();

      });

      $('a.edit').click(function(e) {
         e.preventDefault();

         editorForm.attr('action', $(this).attr('href'));
         TextEditor.val('').focus();

         window.scrollTo(0, editorDiv.offset().top);

         var data = $('#' + $(this).attr('id').replace('edit', 'raw'));
         TextEditor.val(data.find('pre.postbody').html());
      });

      $('a.reply').click(function(e) {
         e.preventDefault();

         editorForm.attr('action', $(this).attr('href'));
         TextEditor.val('').focus();

         window.scrollTo(0, editorDiv.offset().top);
      });

      $('a.delete').click(function(e) {

         var answer = confirm("此操作不可恢复，您确认要删除该内容吗？");
         if (!answer)
         {
            e.preventDefault();
         }
      });

      $('#ajax-file-upload').click(function(e) {
         var file = $('#ajax-file-select');
         if (file.val().length > 0)
         {
            file.upload('/file/ajax/upload', function(res) {
               try {
                  if (res.error && res.error.length > 0) {
                     var msg = '';
                     if (Object.prototype.toString.call(res.error) === '[object Array]') {
                        for (var i = 0; i < res.error.length; i++) {
                           msg = msg + res.error[i].name + ' : ' + res.error[i].error + "\n";
                        }
                     }
                     else // string
                     {
                        msg = res.error;
                     }
                     alert(msg);
                  }

                  if (res.saved && res.saved.length > 0) {
                     fileTable.show();
                     for (var i = 0; i < res.saved.length; i++) {
                        var path = res.saved[i].path;
                        var imageExt = new Array('jpeg', 'gif', 'png');
                        var fileExt = path.split('.').pop();
                        var bbcode;

                        if (imageExt.indexOf(fileExt) >= 0) {
                           bbcode = '[img]' + path + '[/img]';
                        }
                        else {
                           bbcode = '[file="' + path + '"]' + res.saved[i].name + '[/file]';
                        }

                        var row = '<tr id="editfile-' + path + '">' +
                            '<td><input type="text" maxlength="30" name="files[' + path + '][name]" id="editfile-' + path + '-name" size="30" value="' + res.saved[i].name + '" class="form-text"></td>' +
                            '<td style="padding: 0 10px;">' + bbcode + '<input type="text" style="display:none;" name="files[' + path + '][path]" value="' + path + '"></td>' +
                            '<td style="text-align: center;"><a href="/file/delete?id=' + path + '" class="ajax-file-delete" id="editfile-' + path + '-delete">X</a></td>' +
                            '</tr>';
                        fileTableBody.append(row);
                     }
                  }
               }
               catch (e)
               {
                  alert('您的浏览器在上传文件过程中遇到错误，请换用其他浏览器上传文件。');
                  $.post('/bug/ajax-file-upload', 'error=' + e.message + '&res=' + encodeURIComponent(res));
               }
            }, 'json');
         }
      });

      fileTable.on("click", ".ajax-file-delete", function(e) {
         //$(".ajax-file-delete", fileTable).live("click", function(e) {
         e.preventDefault();
         //alert('"' + this.id.replace('-delete', '') + '"');
         var row = this.parentNode.parentNode;
         var table = row.parentNode.parentNode;
         //alert(row.sectionRowIndex);
         table.deleteRow(row.rowIndex);
         if (table.rows.length <= 1)
         {
            fileTable.hide();
         }
      });
   }

   $('a.view_switch').click(function(e) {
      var view = $(this).attr('href');
      if (view.length > 1)
      {
         $.cookie('umode', view.substring(1));
         $(this).attr('href', window.location.pathname);
      }
      else
      {
         e.preventDefault();
      }
   });
});