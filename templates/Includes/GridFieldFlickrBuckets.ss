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

<tbody class="ss-gridfield-items" data-flickr-set-id="$ID"  id="buckets">


<tr><td>
<p>Images will appear here grouped by the time difference selected in the slider above</p>
</td></tr>

<tfoot><tr>
	<td class="bottom-all" colspan="2">


		<span class="pagination-records-number">
			View
			1 - 27
			of
			27 @todo FIX
		</span>
	</td>
</tr></tfoot>

</tbody></table>




<div id="hiddenImageStore">
<h2>Image Store</h2>
<table class="bucket" id="bucket_0">
<tr>
<% loop $FlickrPhotosNotInBucket %>
<td id="flickrPhoto_$ID" data-time="$TakenAt" class="bucketPhoto">
<img data-id="$ID" src="$ThumbnailURL" alt="$Title" title="$Title" class="imgDrag flickrThumbnail"
	 data-flickr-preview-url="$ProtocolAgnosticLargeURL"
	 data-flickr-preview-width="$LargeWidth"
	 data-flickr-preview-width="$LargeHeight"
	 style="width:{$ThumbnailWidth}px;"/>
</td>
</tr>
<% end_loop %>
</table>
</div>
