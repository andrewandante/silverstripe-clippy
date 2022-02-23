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

    /**
     * @see LeftAndMain::show()
     * @return HTTPResponse
     * @throws HTTPResponse_Exception
     */
    public function markdown(HTTPRequest $request)
    {
        if ($request->param('ID')) {
            $this->setCurrentPageID($request->param('ID'));
        }

        $pageID = $this->currentPageID();
        $response = $this->getResponse();
        $response->addHeader('Content-Type', 'application/json');
        $response->setBody(json_encode([
            'ID' => $pageID,
            'Content' => $this->getUserGuideContent(),
        ]));

        return $response;
    }

    /**
     * Here we are overriding the show method in order to force a page reload if
     * ugid is set in the URL. It would be better to just trigger a reload of
     * the preview panel
     *
     * @param $request
     * @return mixed
     */
    public function show($request)
    {
        $response = parent::show($request);
        if ($request->getVar('ugid')) {
            $response->addHeader('X-Reload', true);
            $response->addHeader('X-ControllerURL', $request->getURL(true));
        }
        return $response;
    }
}
