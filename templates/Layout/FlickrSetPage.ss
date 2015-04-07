$EnsureMenuHidden
<a href="#flickrSetNavigation" class="downThePageNavigation">&darr;&nbsp;Navigation</a>
<h1>$Title</h1>
$Description

$BasicMap

<ul class="imageList">
<% control FlickrPhotos %>
<li class="slide orientation{$Orientation}">

<div class="slideTitle">$TakenAt.Nice</div>
<a id="photo_$ID" href="$LargeURL" title="$Title"  class="lightbox" data-flickr-large-url="$LargeURL" data-flickr-medium-url="$MediumURL" >
<img src="$ThumbnailURL"
style="height:{$ThumbnailHeight}px; width:{$ThumbnailWidth}px; margin-left:{$HorizontalMargin(120)}px;margin-top:{$VerticalMargin(120)}px;"/>
</a>

 <div class="slideExif">
 <span class="iso"><% if ISO %>ISO{$ISO}<% end_if %></span>

<% if ShutterSpeed %>{$ShutterSpeed}s<% end_if %>
 </div>





<div class="slideLargeCaption hide">
<span class="exif">f$Aperture
<% if ShutterSpeed %>{$ShutterSpeed}s<% end_if %>
<% if ISO %>ISO{$ISO}<% end_if %>
$TakenAt.Nice
</span>


<div class="phototags">
<% control FlickrTags %>
<a href="/tags/photo/$Value">$RawValue</a>
<% end_control %>
</div>
</div>

</li><% end_control %>
</ul>
<div id="flickrSetNavigation">&nbsp;</dv>
<% include FolderWithImagesNavigation %>
<% include ParentFolderWithImagesNavigation %>

<% include MainContentFooter %>
