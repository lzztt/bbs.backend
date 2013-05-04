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
