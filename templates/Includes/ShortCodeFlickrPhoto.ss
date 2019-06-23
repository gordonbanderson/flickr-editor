<div class="imageWithCaption centercontents <% if Position == left %>pull-left span4<% end_if %><% if Position == right %>pull-right span4<% end_if %>">
<img src="$FlickrImage.ProtocolAgnosticLargeURL" alt="$Title" title="$Title">
<div class="meta">
	<p class="exif"><a href="http://www.flickr.com/photos/<% if $FlickrImage.Photographer %>$FlickrImage.Photographer.PathAlias<% else %>gordonbanderson<% end_if %>/{$FlickrImage.FlickrID}/in/photostream" target="_flickr">
		<% include Utils/FontAwesomeIcon Icon='flickr' %></a>f{$FlickrImage.Aperture} {$FlickrImage.ShutterSpeed}s $FlickrImage.TakenAt.Nice</p>
	<p class="caption">$Caption<% if $FlickrImage.Photographer %><% if $FlickrImage.Photographer %>&nbsp;&nbsp;[$FlickrImage.Photographer.DisplayName]<% end_if %><% end_if %></p>
</div>
</div>
