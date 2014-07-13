<table><tbody>
<?php foreach ($files as $f): ?>
<tr id="editfile-<?php print $f['fid']; ?>">
<td><input type="checkbox" class="form-checkbox" <?php if ($f['list']): ?>checked="checked" <?php endif; ?> value="1" id="editfile-<?php print $f['fid']; ?>-list" name="files[<?php print $f['fid']; ?>][list]" /></td>
<td><input type="text" class="form-text" value="<?php print $f['name']; ?>" size="30" id="editfile-<?php print $f['fid']; ?>-name" name="files[<?php print $f['fid']; ?>][name]" maxlength="30" /></td>
<td style="padding: 0 10px;">[img]<?php print $f['path']; ?>[/img]<input type="text" value="<?php print $f['path']; ?>" name="files[<?php print $f['fid']; ?>][path]" style="display:none;" /></td>
<td style="text-align: center;"><a id="editfile-<?php print $f['fid']; ?>-delete" class="ajax-file-delete" href="/file/delete/<?php print $f['fid']; ?>">X</a></td>
</tr>
<?php endforeach; ?>
</tbody></table>
