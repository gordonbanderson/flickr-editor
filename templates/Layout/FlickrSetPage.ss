<h1>$Title</h1>
$Description



<% if HasGeo %>$Map<% end_if %>

<ul class="imageList">
<% control FlickrPhotos %>
<li class="slide orientation{$Orientation}">

<div class="slideTitle">$TakenAt.Nice</div>
<a id="photo_$ID" href="$FlickrPageURL"  class="highslide" data-flickr-large-url="$LargeURL" data-flickr-medium-url="$MediumURL" >
<img src="$ThumbnailURL"
style="height:{$ThumbnailHeight}px; width:{$ThumbnailWidth}px; margin-left:{$HorizontalMargin(120)}px;margin-top:{$VerticalMargin(120)}px;"/>
</a>

 <div class="slideExif">
 <span class="iso"><% if ISO %>ISO{$ISO}<% end_if %></span>

<% if ShutterSpeed %>{$ShutterSpeed}s<% end_if %>
 </div>             




 
<div class="highslide-caption">
<div class="hiddenDescription" id="photoDescription_$ID">$Description</div>
<h5>$Title</h5>
 <button class="btn btn-mini btn-primary showDescriptionButton" type="button">Show Description</button>
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

<% if IsLive %><% include ProdHighslide_js %><% else %><% include DevHighslide_js %><% end_if %>
