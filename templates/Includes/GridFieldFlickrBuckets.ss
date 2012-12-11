<table class="ss-gridfield-table" cellpadding="0" cellspacing="0"><thead><tr class="title">
	<th colspan="2">
		<h2>Edit Photo Groups</h2>
		<div class="right"></div>
		<div class="left"></div>
	</th>
</tr>
<tr class="sortable-header">
		<th class="main col-Bucket"><span class="non-sortable">Bucket</span></th>
		<th class="main col-Actions"><button name="showFilter" class="ss-gridfield-button-filter trigger ss-ui-button ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" role="button" aria-disabled="false"><span class="ui-button-text"></span></button></th>
</tr>

</thead>


<tbody>
<tr>
<td colspan="2">
1s <input id="bucketTimeProgressBar" type="range" min="1" max="240" /> 240s
<div id="selectedBucketTime">&nbsp;</div>
</td>
</tr>
</tbody>

<tbody class="ss-gridfield-items" id="buckets">


<tr class="ss-gridfield-item first odd" data-id="167" data-class="FlickrPhoto" style="cursor: default;">
<td class="col-Thumbnail">****<img src="http://farm9.staticflickr.com/8208/8232426960_3353550603_t.jpg"></td>
<td class="col-buttons">
<a class="action action-detail edit-link" 
href="admin/flickr_sets/FlickrSet/EditForm/field/FlickrSet/item/4/ItemEditForm/field/Flickr Photos/item/167/edit" title="Edit">edit</a>
<button name="action_gridFieldAlterAction?StateID=50bddd55ef02c6_52235108" 
class="action action gridfield-button-delete nolabel ss-ui-button ui-button ui-widget
 ui-state-default ui-corner-all ui-button-text-icon-primary" 
 id="action_DeleteRecord167" title="Delete" 
 data-icon="cross-circle" 
 data-url="admin/flickr_sets/FlickrSet/EditForm/field/FlickrSet/item/4/ItemEditForm/field/Flickr Photos"
  role="button" aria-disabled="false"><span class="ui-button-icon-primary ui-icon btn-icon-cross-circle"></span><span class="ui-button-text">
		
	</span></button>
</td></tr>

<tfoot><tr>
	<td class="bottom-all" colspan="2">
		
		
		<span class="pagination-records-number">
			View
			1 - 27
			of 
			27
		</span>
	</td>
</tr></tfoot>

</tbody></table>




<div id="hiddenImageStore">
<h2>Image Store</h2>
<table class="bucket" id="bucket_0">
<tr>
<% control FlickrPhotos %>
<td id="flickrPhoto_$ID" data-time="$TakenAt" class="bucketPhoto">
<img src="$ThumbnailURL" alt="$Title" title="$Title" style="width:{$ThumbnailWidth}px;"/>
</td>
</tr>
<% end_control %>
</table>
</div>