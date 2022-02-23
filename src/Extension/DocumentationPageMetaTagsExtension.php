<?php

namespace SilverStripe\Clippy\Extension;

use SilverStripe\Control\Controller;
use SilverStripe\Core\Extension;

class DocumentationPageMetaTagsExtension extends Extension
{
    /**
     * This method looks for the param EditPageID and if set, updates some meta tags in the CMSPreview
     * @see SiteTreePreviewLinkExtension
     *
     * This prevents the CMS from trying to redirect to the Documentation Page when it's in the Preview
     *
     * @param string $tags
     */
    public function MetaTags(&$tags)
    {
        $request = Controller::curr()->getRequest();

        if ($currentEditPageID = $request->getVar('EditPageID')) {
            $origIDTag = "<meta name=\"x-page-id\" content=\"{$this->owner->ID}\" />\n";
            $origEditLinkTag = "<meta name=\"x-cms-edit-link\" content=\"" . $this->owner->CMSEditLink() . "\" />\n";

            $modifiedCMSEditLink = str_replace($this->owner->ID, $currentEditPageID, $this->owner->CMSEditLink());

            $newIDTag = "<meta name=\"x-page-id\" content=\"{$currentEditPageID}\" />\n";
            $newEditLinkTag = "<meta name=\"x-cms-edit-link\" content=\"" . $modifiedCMSEditLink . "\" />\n";

            $tags = str_replace($origIDTag, $newIDTag, $tags);
            $tags = str_replace($origEditLinkTag, $newEditLinkTag, $tags);
        }
    }
}
