<header>
    <div class="container">
        <h1>$Title</h1>
    </div>
</header>

<main>
    <article>
        <div class="container">
            <div class="userguide">
                <span class="menu__hamburglar"></span>
                <div class="userguide__menu navigation navigation--secondary">
                    <h4 class="cuserguide__menu-title">Menu</h4>
                    <span class="close"></span>
                    <% if $Navigation %>
                        <% include SilverStripe\Clippy\Includes\DocumentationPageMenu List=$Navigation %>
                    <% end_if %>
                </div>
                <div class="userguide__content">
                    $Content
                </div>
            </div>
        </div>
    </article>

    <% if $Form %>
        $Form
    <% end_if %>
</main>
