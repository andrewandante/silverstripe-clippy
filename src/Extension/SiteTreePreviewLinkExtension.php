<?php

namespace SilverStripe\Clippy\Extension;

use SilverStripe\Clippy\Model\UserGuide;
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
            $urlSegment = DocumentationPage::get()->first()->Link('viewdoc');
            $ugid = $controller->getRequest()->getVar('ugid');
            if ($ugid !== null) {
                /** @var UserGuide $guide */
                $guide = UserGuide::get()->byID($ugid);
                if ($guide && $guide->exists()) {
                    $urlSegment .= $guide->getUrlSegment();
                }
            }
            $link = Controller::join_links(
                $urlSegment,
                '?EditPageID=' . $this->owner->ID,
                '?Stage=Live',
                '?CMSPreview=1'
            );
        }

        return [$link, $action];
    }

}
