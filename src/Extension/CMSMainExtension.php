<?php

namespace SilverStripe\Clippy\Extension;

use SilverStripe\Clippy\Controllers\DocumentationPageController;
use SilverStripe\Control\Controller;
use SilverStripe\Core\Extension;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\Clippy\Model\UserGuide;
use SilverStripe\Clippy\Controllers\CMSUserGuideController;
use Page;

class CMSMainExtension extends Extension
{

    public function getLinkPageUserGuide(): ?string
    {
        $owner = $this->getOwner();
        if ($id = $owner->currentRecordID()) {
            return $owner->LinkWithSearch(
                Controller::join_links(CMSUserGuideController::singleton()->Link('show'), $id)
            );
        } else {
            return null;
        }
    }

    public function getIsUserGuideController(): bool
    {
        return get_class($this->getOwner()) === CMSUserGuideController::class;
    }

    public function getHasUserGuides(): bool
    {
        $id = $this->owner->currentRecordID();

        if ($id) {
            $page = Page::get()->setUseCache(true)->byID($id);
        }

        if ($page) {
            $userguides = UserGuide::get()->filter('DerivedClass', $page->ClassName);           
            return $userguides && $userguides->count() > 0;
        }

        return false;
    }
}
