<p>Group photographs by time and edit them together.  This is useful in situations where you take multiple shots of the same
subject rapidly in succession</p>
1s <input id="bucketTimeProgressBar" type="range" min="1" max="60" /> 60s
<div id="selectedBucketTime"></div>

<div id="buckets">
Buckets will appear here
</div>

<div id="hiddenImageStore">
<h2>Image Store</h2>
<ul class="bucket" id="bucket_0">
<% control FlickrPhotos %>
<li id="flickrPhoto_$ID" data-time="$TakenAt" class="bucketPhoto">
<img src="$ThumbnailURL" alt="$Title" title="$Title" style="width:{$ThumbnailWidth}px;"/>
</li>
<% end_control %>
</ul>
</div>