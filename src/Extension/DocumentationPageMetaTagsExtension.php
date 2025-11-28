<?php

namespace SilverStripe\Clippy\Extension;

use SilverStripe\Control\Controller;
use SilverStripe\Core\Extension;

class DocumentationPageMetaTagsExtension extends Extension
{
    /**
     * This method looks for the param EditPageID and if set, updates some meta components in the CMSPreview
     * @see SiteTreePreviewLinkExtension
     *
     * This prevents the CMS from trying to redirect to the Documentation Page when it's in the Preview
     *
     * @param string $tags
     */
    public function updateMetaComponents(&$tags)
    {
        $request = Controller::curr()->getRequest();
        $currentEditPageID = $request->getVar('EditPageID');
        if ($currentEditPageID === null) {
            $currentEditPageID = $request->getSession()->get('EditPageID');
        }

        if ($currentEditPageID !== null) {
            $request->getSession()->set('EditPageID', $currentEditPageID);

            if (isset($tags['pageId'])) {
                $tags['pageId']['attributes']['content'] = $currentEditPageID;
            }

            if (isset($tags['cmsEditLink'])) {
                $tags['cmsEditLink']['attributes']['content'] = str_replace($this->owner->ID, $currentEditPageID, $this->owner->CMSEditLink());
            }
        } else {
            $request->getSession()->clear('EditPageID');
        }
    }
}
