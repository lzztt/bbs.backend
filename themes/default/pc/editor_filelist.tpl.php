<table><tbody>
<?php foreach ($files as $f): ?>
<tr id="editfile-<?php echo $f['fid']; ?>">
<td><input type="checkbox" class="form-checkbox" <?php if ($f['list']): ?>checked="checked" <?php endif; ?> value="1" id="editfile-<?php echo $f['fid']; ?>-list" name="files[<?php echo $f['fid']; ?>][list]" /></td>
<td><input type="text" class="form-text" value="<?php echo $f['name']; ?>" size="30" id="editfile-<?php echo $f['fid']; ?>-name" name="files[<?php echo $f['fid']; ?>][name]" maxlength="30" /></td>
<td style="padding: 0 10px;">[img]<?php echo $f['path']; ?>[/img]<input type="text" value="<?php echo $f['path']; ?>" name="files[<?php echo $f['fid']; ?>][path]" style="display:none;" /></td>
<td style="text-align: center;"><a id="editfile-<?php echo $f['fid']; ?>-delete" class="ajax-file-delete" href="/file/delete/<?php echo $f['fid']; ?>">X</a></td>
</tr>
<?php endforeach; ?>
</tbody></table>
