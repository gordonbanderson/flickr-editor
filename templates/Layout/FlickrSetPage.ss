<h1>$Title</h1>
$Description



<% if HasGeo %>$Map<% end_if %>

<ul class="imageList">
<% control FlickrPhotos %>
<li class="slide orientation{$Orientation}">

<div class="slideTitle">$TakenAt.Nice</div>
 <div class="slideExif">
 <span class="iso"><% if ISO %>ISO{$ISO}<% end_if %></span>

<% if ShutterSpeed %>{$ShutterSpeed}s<% end_if %>
 </div>             

<a id="photo_$ID" href="$FlickrPageURL"  class="highslide">
<img src="$ThumbnailURL"
style="height:{$ThumbnailHeight}px; width:{$ThumbnailWidth}px; margin-left:{$HorizontalMargin(120)}px;margin-top:{$VerticalMargin(120)}px;"/>

</a>


 
<div class="highslide-caption">$Title

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

<script type="text/javascript">
JQ = jQuery.noConflict();

JQ(document).ready(function() {

	// change the links to these for highslide but leave as the pages for non JS
	imageURLs = $IdToUrlJson;



	hs.graphicsDir = '/themes/wot/hs-furniture/';
	hs.align = 'center';
	hs.transitions = ['expand', 'crossfade'];
	hs.outlineType = 'rounded-white';
	hs.fadeInOut = true;
	//hs.dimmingOpacity = 0.75;

	// Add the controlbar
	hs.addSlideshow({
		//slideshowGroup: 'group1',
		interval: 5000,
		repeat: false,
		useControls: true,
		fixedControls: 'fit',
		overlayOptions: {
		opacity: 0.75,
		position: 'bottom center',
		hideOnMouseOut: true
	}
});

<% if _HasCoordinates %>
//initializeMap();
<% end_if %>

JQ('a.highslide').each(function() {
	var image_id = JQ(this).attr('id')
	image_id = image_id.replace('photo_','');
;	////console.log("IMAGE ID:"+image_id);
	var imageURL = imageURLs[image_id];
	JQ(this).attr('href', imageURL);
	////console.log(imageURL);
	this.onclick = function() { 
		return hs.expand(this); 
	}; 
});
});
</script>