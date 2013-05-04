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