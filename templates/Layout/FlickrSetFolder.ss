<h1>$Title</h1>
$Description


<ul class="media-list">
<% loop Children %>
<li class="media">
<a class="pull-left" href="#">
<% with FlickrSetForPage %>
<% with PrimaryFlickrPhoto %><img class="media-object" src="$ThumbnailURL"><% end_with %><% end_with %></a>
<div class="media-body">
<h4 class="media-heading"><a href="$Link">$Title</a></h4>
<p>$Description</p>
</li>
<% end_loop %>
</ul>