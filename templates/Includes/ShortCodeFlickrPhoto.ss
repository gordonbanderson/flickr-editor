<div class="imageWithCaption centercontents <% if Position == left %>pull-left span4<% end_if %><% if Position == right %>pull-right span4<% end_if %>">
<img src="$FlickrImage.LargeURL">
<div class="meta">
	<p class="exif"><a href="http://www.flickr.com/photos/gordonbanderson/{$FlickrImage.FlickrID}/in/photostream" target="_flickr"><img class="flickrLink" src="/themes/wot/img/flickr.png" alt="Flickr"/></a>f{$FlickrImage.Aperture} $FlickrImage.TakenAt.Nice</p>
	<p class="caption">$FlickrImage.Title</p>
</div>
</div>
<% if Position == center %><div class="clearall">&nbsp;</div><% end_if %>