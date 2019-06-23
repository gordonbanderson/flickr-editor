<% require css("weboftalent/flickr:css/flickr.css") %>
<% require css("weboftalent/flickr:thirdparty/photoswipe/photoswipe.css") %>
<% require css("weboftalent/flickr:thirdparty/photoswipe/default-skin/default-skin.css") %>

<% require javascript("weboftalent/flickr:thirdparty/photoswipe/photoswipe.min.js") %>
<% require javascript("weboftalent/flickr:thirdparty/photoswipe/photoswipe-ui-default.min.js") %>
<% require javascript("weboftalent/flickr:javascript/flickrswipe.js") %>


<a href="#flickrSetNavigation" class="downThePageNavigation">&darr;&nbsp;Navigation</a>
<h1>$Title</h1>
$Description

<div class="gallery" itemscope itemtype="http://schema.org/ImageGallery">
 <% loop $FlickrSetForPage.FlickrPhotos.Sort(TakenAt)  %>
 <figure itemprop="associatedMedia" itemscope itemtype="http://schema.org/ImageObject" class="slide orientation{$Orientation}">
  <a id="photo_$ID" href="$LargeURL" title="$Title" data-size="{$LargeWidth}x{$LargeHeight}">
   <img src="$ThumbnailURL" itemprop="thumbnail" height="$ThumbnailHeight" width="$ThumbnailWidth" alt="$Title"
        style="height:{$ThumbnailHeight}px; width:{$ThumbnailWidth}px; margin-left:{$HorizontalMargin(120)}px;margin-top:{$VerticalMargin(120)}px;"/>
  </a>
 </figure>
<% end_loop %>
</div>

<% include Includes/PhotoSwipeCore %>
