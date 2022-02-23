<?php

namespace SilverStripe\Clippy\Extension;

use SilverStripe\Control\Controller;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\Clippy\Model\UserGuide;
use SilverStripe\Clippy\Controllers\CMSUserGuideController;
use Page;

class CMSMainExtension extends Extension
{

    public function LinkPageUserGuide()
    {
        $owner = $this->getOwner();
        if ($id = $owner->currentPageID()) {
            return $owner->LinkWithSearch(
                Controller::join_links(CMSUserGuideController::singleton()->Link('show'), $id)
            );
        } else {
            return null;
        }
    }

    public function IsUserGuideController()
    {
        return get_class($this->getOwner()) === CMSUserGuideController::class;
    }

    public function ShowUserGuide()
    {
        return HTMLEditorField::create('UserGuideContent', 'User Guide Content', $this->getUserGuideContent())
            ->performReadonlyTransformation();
    }

    public function getUserGuideContent()
    {
        $ugid = $this->getOwner()->getRequest()->getVar('ugid');
        $pageID = $this->getOwner()->currentPageID();

        if (!$pageID) {
            return null;
        }

        $page = Page::get()->find('ID', $pageID);

        if (!$page) {
            return null;
        }

        if ($ugid !== null) {
            $userguide = UserGuide::get_by_id($ugid);

            if ($userguide->exists() && $userguide->DerivedClass === $page->ClassName) {
                return $userguide->Content;
            }
        }

        $defaultUserguide = UserGuide::get()->find('DerivedClass', $page->ClassName);

        return $defaultUserguide && $defaultUserguide->exists() ? $defaultUserguide->Content : null;
    }
}
