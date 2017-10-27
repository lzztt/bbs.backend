<table><tbody>
<?php foreach ($files as $f): ?>
<tr id="editfile-<?= $f['fid'] ?>">
<td><input type="checkbox" class="form-checkbox" <?php if ($f['list']): ?>checked="checked" <?php endif ?> value="1" id="editfile-<?= $f['fid'] ?>-list" name="files[<?= $f['fid'] ?>][list]" /></td>
<td><input type="text" class="form-text" value="<?= $f['name'] ?>" size="30" id="editfile-<?= $f['fid'] ?>-name" name="files[<?= $f['fid'] ?>][name]" maxlength="30" /></td>
<td style="padding: 0 10px;">[img]<?= $f['path'] ?>[/img]<input type="text" value="<?= $f['path'] ?>" name="files[<?= $f['fid'] ?>][path]" style="display:none;" /></td>
<td style="text-align: center;"><a id="editfile-<?= $f['fid'] ?>-delete" class="ajax-file-delete" href="/file/delete/<?= $f['fid'] ?>">X</a></td>
</tr>
<?php endforeach ?>
</tbody></table>
