<?php

namespace SilverStripe\Clippy\Controllers;

use DOMDocument;
use PageController;
use SilverStripe\Clippy\Model\UserGuide;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Path;
use SilverStripe\Model\ArrayData;
use SilverStripe\Model\List\ArrayList;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\Security\Permission;
use SilverStripe\Security\Security;

/**
 * This page type allows for display of .md documentation files as html content within a web page.
 * Document located in the app/docs/ directory will be parsed and rendered within this page's template.
 * This page type is not intended for public consumption; it is viewable only by logged-in users with
 * CMS_ACCESS permissions.
 */
class DocumentationPageController extends PageController
{

    private static array $allowed_actions = [
        'viewdoc',
        'streamInImage',
    ];

    private static array $url_handlers = [
        'viewdoc/$*' => 'viewdoc',
    ];

    /**
     * Only allow those with CMS access to view this page
     */
    public function init(): bool
    {
        parent::init();

        if (Permission::check('CMS_ACCESS')) {
            return true;
        }

        Security::permissionFailure();

        return false;
    }

    /**
     * Define data to sent to template when viewdoc action is called
     * todo: get DRY happening here
     *
     * @return array
     */
    public function viewdoc(): array
    {
        return [
            'Navigation' => $this->getNavigation(),
            'Content' => DBHTMLText::create()->setValue($this->getContent()),
        ];
    }

    public function streamInImage(HTTPRequest $request): string
    {
        $streamInImage = $request->getVar('imagePath');
        if (file_exists($streamInImage)) {
            return file_get_contents($streamInImage);
        }
        return 'Image not found';
    }

    /**
     * Define path of directory where md docs reside
     *
     * @return string
     */
    public function getPath(): string
    {
        return Path::join(
            BASE_PATH,
            $this->Config()->get('docs_dir')
        );
    }

    /**
     * Define path of directory where screenshots reside
     *
     * @return string
     */
    public function getScreenshotsDirPath(): string
    {
        return Path::join(
            BASE_PATH,
            $this->Config()->get('screenshots_dir')
        );
    }

    /**
     * Get index to display as navigation menu.
     * This parses the converted markdown (html) from _index.md and constructs as
     * ArrayList which can be iterated over (in a frontend template, for example).
     *
     * @return ArrayList
     */
    public function getNavigation(): ArrayList
    {
        $html = $this->getConvertedMD('_index.md');

        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->loadHTML($html);
        $list = $dom->getElementsByTagName('ul')->item(0);

        return $this->getListData($list);
    }

    /**
     * Iterate over a 'ul' DOMElement and add data from its 'li' childNodes to an ArrayList.
     * Specifically, the Title and Href data is obtained from the 'a' tags within those 'li' nodes.
     * Also recursively checks for nested ul DOMElements and adds them as children to th ArrayList's items.
     *
     * @param object $list DOMElement 'ul'
     * @return ArrayList
     */
    public function getListData($list): ArrayList
    {
        $navData = ArrayList::create();
        if (!is_object($list) || !$list->childNodes) {
            return $navData;
        }
        foreach ($list->childNodes as $child) {

            // we only care about 'li' children (not newlines etc - DOMDocument gives us all sorts of stuff)
            if ($child->nodeName === 'li') {
                $data = [];

                // get data from the (first) 'a' tag contained in this 'li'
                if ($child->getElementsByTagName('a')->item(0)) {
                    $link = $child->getElementsByTagName('a')->item(0);
                    $data['Title'] = $link->nodeValue;
                    $data['Link'] = $link->getAttribute('href');
                }

                // recursively add any nested 'ul' nodes
                if ($child->getElementsByTagName('ul')->item(0)) {
                    $data['Children'] = $this->getListData($child->getElementsByTagName('ul')->item(0));
                }

                if (count($data) > 0) {
                    $navData->push(ArrayData::create($data));
                }
            }
        }

        return $navData;
    }

    /**
     * Define filename of document being requested as params after viewdoc
     *
     * @return string
     */
    public function getFileName(): string
    {
        $filename = 'introduction.md';
        $request = $this->getRequest();
        $action = $request->param('Action');

        if (isset($action) && $action === 'viewdoc' && $request->remaining() !== '') {
            $filename =  $request->remaining() . '.md';
        }

        return $filename;
    }

    /**
     * retrieve contents of requested .md doc, converted to html string
     *
     * @return string
     */
    public function getContent(): string
    {
        return DBHTMLText::create()->setValue($this->getConvertedMD($this->getFileName()));
    }
    /**
     * Checks for the existence of .md doc with supplied filepath
     * returns its contents converted to html string if file is found.
     * Falls back to default message if file is not found.
     *
     * @param string $filename
     * @return string
     */
    public function getConvertedMD($filename): string
    {
        $filepath = Path::join($this->getPath(), $filename);
        $guide = UserGuide::get()->filter('MarkdownPath', $filepath)->first();

        if ($filepath && $guide && $guide->exists() && $guide->Content) {
            return $guide->Content;
        } else {
            return '<h1>Not Found...</h1>
                <hr/>
                <p>Sorry, there is no documentation available for the requested url.</p>';
        }
    }

    /**
     * Define data to sent to template when no action is called
     *
     * @return array
     */
    protected function index(): array
    {
        return [
            'Navigation' => $this->getNavigation(),
            'Content' => DBHTMLText::create()->setValue($this->getContent()),
        ];
    }

}
