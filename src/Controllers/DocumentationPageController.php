<?php

namespace SilverStripe\Clippy\Controllers;

use League\CommonMark\GithubFlavoredMarkdownConverter;
use PageController;
use SilverStripe\Core\Path;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\Security\Permission;
use SilverStripe\Security\Security;
use SilverStripe\View\ArrayData;

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
            '/',
            RESOURCES_DIR,
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

        $dom = new \DOMDocument();
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
    }

    /**
     * Define filename of document being requested via url param "ID"
     *
     * @return string
     */
    public function getFileName(): string
    {
        $filename = 'introduction.md';
        $params = $this->getURLParams();

        if (isset($params['Action']) && $params['Action'] === 'viewdoc') {
            if (isset($params['ID'])) {
                $filename = $params['ID'] . '.md';
            }
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
        $filename = $this->getFileName();

        // Replace filepath variable with configured file path
        $content = str_replace(
            '<img src="$screenshots_dir',
            '<img src="' . $this->getScreenshotsDirPath(),
            $this->getConvertedMD($filename)
        );

        return DBHTMLText::create()->setValue($content);
    }

    /**
     * Checks for the existence of .md doc with supplied filename and
     * returns its contents converted to html string if file is found.
     * Falls back to default message if file is not found.
     *
     * @param string $filename
     * @return string
     */
    public function getConvertedMD($filename): string
    {
        $path = $this->getPath();

        $converter = new GithubFlavoredMarkdownConverter([
            'html_input' => 'strip',
            'allow_unsafe_links' => true,
        ]);

        if (file_exists(Path::join($path, $filename))) {
            $raw = file_get_contents(Path::join($path, $filename));
            $converted = $converter->convertToHtml($raw);
            $content = $converted->getContent();
        } else {
            $content = '<h1>Not Found...</h1>
                <hr/>
                <p>Sorry, there is no documentation available for the requested url.</p>';
        }

        return $content;
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

