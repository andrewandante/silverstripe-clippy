<?php

namespace SilverStripe\Clippy\Controllers;

use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Security\Security;

class UserGuideController extends Controller
{

    private static $url_handlers = [
        'filePath' => 'index',
        'streamInImage' => 'index',
    ];

    public function index(HTTPRequest $request)
    {
        // This ensures the images etc are protected by login
        if (!Security::getCurrentUser()) {
            return Security::permissionFailure();
        }

        // @TODO do something with this?
        //  $filePath = $request->getVar('filePath');

        // Proxy for images in markdown files
        $streamInImage = $request->getVar('streamInImage');
        if (file_exists(BASE_PATH . $streamInImage)) {
            return file_get_contents(BASE_PATH . $streamInImage);
        }
    }
}
