<input $AttributesHTML />
<input type="text" class="imageFlickrID" size=10 value="$FlickrID" />
<div class="chosenFlickrImage">
<% if $FlickrID %>
<h4>$FlickrTitle</h4>
<img src="$MediumURL" alt="$Title"/>
<% else %>
<p>Your image will appear here</p>
<% end_if %>
</div>

