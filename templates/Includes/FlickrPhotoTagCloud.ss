<ul class="tagCloud">
<% loop $TagCloud %>
<li class="tag{$Size}"><a href="{$Top.Link}?{$Params}">$Name</a></li>
<% end_loop %>
</ul>
