<?php

namespace SilverStripe\Clippy\Extension;

use SilverStripe\Admin\CMSMenu;
use SilverStripe\Admin\LeftAndMainExtension;
use SilverStripe\Clippy\PageTypes\DocumentationPage;
use SilverStripe\Core\Config\Config;

class DocumentationPageLeftAndMain extends LeftAndMainExtension
{

    private static string $menu_icon = 'font-icon-block-media';

    public function init()
    {
        // unique identifier for this item. Will have an ID of Menu-$ID
        $id = 'LinkToDocumentationPage';

        // your 'nice' title
        $title = 'CMS User Guide';

        // the link you want to item to go to
        $link = Config::inst()->get(DocumentationPage::class, 'default_url_segment');

        // priority controls the ordering of the link in the stack. The
        // lower the number, the lower in the list
        $priority = -2;

        // Add your own attributes onto the link. In our case, we want to
        // open the link in a new window (not the original)
        $attributes = [
            'target' => '_blank',
            'title' => 'Bespoke CMS User Guide (opens in a new tab)'
        ];

        $iconClass = 'font-icon-book-open';

        CMSMenu::add_link($id, $title, $link, $priority, $attributes, $iconClass);
    }
}
