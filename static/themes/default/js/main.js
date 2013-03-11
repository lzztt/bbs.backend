/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$(document).ready(function() {

    $('.js_even_odd_parent').each(function() {
        var c = $(this).children();
        c.filter(':even').addClass('even');
        c.filter(':odd').addClass('odd');
    });

    $("ul.sf-menu").superfish();
    /*
     $('#siteLanguage').change(function() {
     $('#siteLanguageForm').submit();
     });
     */
    $('#BBCodeEditor').markItUp(mySettings);

    var BBEditor = $('#BBCodeEditor');
    var titleEditor = $('#edit-title');
    var editorDiv = $('#editor-div');
    var editorForm = $('#editor-form');
    var fileTable = $('#ajax-file-list');
    var fileTableBody = $('tbody', fileTable);
    var TextEditor = $('#TextEditor');

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
        console.log(files);

        if (Object.prototype.toString.call(files) === '[object Array]' && files.length > 0)
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
                    if (Object.prototype.hasOwnProperty.call(res, 'error') && res.error.length > 0) {
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

                    if (Object.prototype.hasOwnProperty.call(res, 'saved') && res.saved.length > 0) {
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

                /*
                 row = $('tbody', $(res)).html();
                 if (row)
                 {
                 fileTable.show();
                 fileTableBody.append(row);
                 file.val('');
                 }
                 else if (res.substr(0, 6) == 'ERROR:')
                 {
                 alert(res.substr(6));
                 }
                 else
                 {
                 alert('您的浏览器在上传文件过程中遇到错误，请换用其他浏览器上传文件。');
                 $.post('/bug/ajax-file-upload', 'error=' + encodeURIComponent(res));
                 }*/

            }, 'json');
        }
    });

    $(".ajax-file-delete", fileTable).live("click", function(e) {
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

    var uid = getCookie('uid');
    if (uid > 0)
    {
        $(".u" + uid).show();

        if (getCookie('urole') === 'super')
        {
            $(".hidden").show();
        }
    }

    var pmCount = getCookie('pmCount');
    if (pmCount > 0)
    {
        $("a#pm").append('<span style="color:red;"> (' + pmCount + ') <span>');
    }

    $('#coin-slider').coinslider({
        effect: 'straight',
        spw: 1,
        sph: 1
    });

    $('a.view_switch').click(function(e) {
        var view = $(this).attr('href');
        if (view.length > 0)
        {
            var expDate = new Date();
            expDate.setFullYear(expDate.getFullYear() + 2);
            view = view.substring(1);

            setCookie('umode', view, expDate, '/');
            $(this).attr('href', window.location.pathname);
        }
        else
        {
            e.preventDefault();
        }


        editorForm.attr('action', $(this).attr('href'));
        TextEditor.val('').focus();

        window.scrollTo(0, editorDiv.offset().top);

        var data = $('#' + $(this).attr('id').replace('edit', 'raw'));
        TextEditor.val(data.find('pre.postbody').html());
    });
});