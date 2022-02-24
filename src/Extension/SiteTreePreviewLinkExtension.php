<?php

namespace SilverStripe\Clippy\Extension;

use SilverStripe\Clippy\PageTypes\DocumentationPage;
use SilverStripe\CMS\Controllers\CMSMain;
use SilverStripe\Control\Controller;
use SilverStripe\Core\Extension;

class SiteTreePreviewLinkExtension extends Extension
{
    /**
     * If we are on the User Guide tab, add the EditPageID var so that we can have a different
     * page in the Edit window and Preview window
     * @see DocumentationPageMetaTagsExtension
     *
     * @param string $link
     * @param string $action
     * @return array
     */
    public function updatePreviewLink(&$link, $action)
    {
        $controller = Controller::curr();
        if ($controller instanceof CMSMain && $controller->getTabIdentifier() === 'guide') {
            $link = Controller::join_links(
                DocumentationPage::get()->first()->RelativeLink(),
                '?EditPageID=' . $this->owner->ID,
                '?Stage=Live',
                '?CMSPreview=1'
            );
        }

        return [$link, $action];
    }

}
