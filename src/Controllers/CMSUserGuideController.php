<?php

namespace SilverStripe\Clippy\Controllers;

use SilverStripe\CMS\Controllers\CMSMain;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Control\HTTPResponse_Exception;
use SilverStripe\Clippy\Model\UserGuide;
use Page;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Clippy\GridField\UserGuideViewer;

class CMSUserGuideController extends CMSMain
{
    private static string $url_segment = 'pages/guide';

    private static string $url_rule = '/$Action/$ID/$OtherID';

    private static int $url_priority = 42;

    private static string $required_permission_codes = 'CMS_ACCESS_CMSMain';

    private static $allowed_actions = [
        'markdown',
        'show',
    ];


    public function getEditForm($id = null, $fields = null)
    {
        $id = $this->currentPageID();
        $page = Page::get_by_id($id);
        $userguides = UserGuide::get()->filter('DerivedClass', $page->ClassName);
        if ($userguides && $userguides->count() > 0) {
            $fields = FieldList::create(
                GridField::create(
                    'Userguides',
                    'User guides',
                    $userguides,
                    UserGuideViewer::create()
                )
            );
        }
        return parent::getEditForm($id, $fields);
    }

    public function getTabIdentifier(): string
    {
        return 'guide';
    }

}
