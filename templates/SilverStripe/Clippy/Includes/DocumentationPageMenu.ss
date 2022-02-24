<ul class="userguide__menu-items">
    <% loop $List %>
        <li class="userguide__menu-item">
            <a class="userguide__menu-link" href="$Link">$Title</a>
            <% if $Children %>
                <% include SilverStripe\Clippy\Includes\DocumentationPageMenu List=$Children %>
            <% end_if %>
        </li>
    <% end_loop %>
</ul>
