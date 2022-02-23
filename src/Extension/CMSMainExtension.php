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
        $controller = Injector::inst()->get(DocumentationPageController::class);
        return $controller->getNavigation() . $controller->getContent();
    }
}
