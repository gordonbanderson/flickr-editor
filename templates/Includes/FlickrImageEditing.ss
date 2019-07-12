<div class="field">
<img class="centered" src="$FlickrPhoto.LargeURL" alt="$Title.XML" title="$Title.XML" style="margin-left:auto; margin-right: auto;"/>
</div>
<% if $FlickrSetID %>
<div class="field">

<button data-flickr-photo-id="{$FlickrPhoto.ID}" data-flickr-set-id="$FlickrSetID"
class="btn action btn-primary font-icon-camera mt-1 mb-4"
id="changeMainPictureButton" data-icon="accept" role="button" aria-disabled="false">
		Make this the main image
</button>
</div>

<% end_if %>
