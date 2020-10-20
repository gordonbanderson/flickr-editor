<div class="searchResult">
    <p class="url">$Record.HighlightedLink.RAW</p>
    <h3><a href="$Record.Link">$Record.ResultTitle.RAW</a></h3>
    <img src="$Record.ThumbnailURL" title="$Record.ResultTitle"/>
    <% loop $Record.Highlights %>
     $Snippet.RAW
    <% end_loop %>
</div>
