<div class="searchResult">
    <p class="url">$Record.HighlightedLink.RAW</p>
    <h3><a href="$Link">$Record.ResultTitle.RAW</a></h3>
    <hr/>
    DEBUG: $ThubmnailURL
    <hr/>
    <img src="$Record.ThumbnailURL" title="$Record.ResultTitle"/>
    <% loop $Record.Highlights %>
     $Snippet.RAW
    <% end_loop %>
</div>
