<?php

namespace SilverStripe\Clippy\Tests\Controllers;

use SilverStripe\Clippy\Controllers\DocumentationPageController;
use SilverStripe\Clippy\PageTypes\DocumentationPage;
use SilverStripe\CMS\Controllers\ModelAsController;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Path;
use SilverStripe\Dev\FunctionalTest;
use SilverStripe\Security\Group;
use SilverStripe\Security\Member;
use SilverStripe\Security\Permission;

class DocumentationPageControllerTest extends FunctionalTest
{

    /**
     * @var string
     */
    protected static $fixture_file = 'DocumentationPageControllerTest.yml';

    /**
     * @var string
     */
    protected $docs_dir = '/app/tests/assets/documents/cms-docs/';

    /**
     * @var string
     */
    protected $screenshots_dir = '/app/tests/assets/documents/cms-docs/';

    /**
     * @var array
     */
    protected $params = [
        'Action' => 'viewdoc',
        'ID' => 'accessing-the-website',
    ];

    /**
     * @var object
     */
    protected $page = null;

    /**
     * @var object
     */
    protected $controller = null;

    /**
     * Set up page, controller and config for use in tests
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->page = $this->objFromFixture(DocumentationPage::class, 'cms_user_guide');
        $this->controller = ModelAsController::controller_for($this->page);
        Config::inst()->merge(DocumentationPageController::class, 'docs_dir', $this->docs_dir);
        Config::inst()->merge(DocumentationPageController::class, 'screenshots_dir', $this->screenshots_dir);
    }

    public function tearDown(): void
    {
        $this->page->destroy();
        $this->page = null;

        $this->controller->destroy();
        $this->controller = null;

        parent::tearDown();
    }

    /**
     * Test visibility of page. It should not be publicly accessible,
     * only viewable by logged-in users with CMS access.
     */
    public function testAccess(): void
    {
        // page should not be publicly accessible
        $this->assertFalse($this->page->canView());

        // If we login as a user with CMS access we should be able to view the page
        $group = $this->objFromFixture(Group::class, 'content_authors');
        Permission::grant($group->ID, 'CMS_ACCESS_CMSMain');
        $member = $this->objFromFixture(Member::class, 'content_author');
        $this->logInAs($member);
        $this->assertTrue($this->page->canView());
    }

    public function testGetPath(): void
    {
        $expected = Path::join(BASE_PATH, $this->docs_dir);
        $this->assertEquals($expected, $this->controller->getPath());
    }

    public function testGetScreenshotsDirPath(): void
    {
        $expected = Path::join('/', RESOURCES_DIR, $this->screenshots_dir);
        $this->assertEquals($expected, $this->controller->getScreenshotsDirPath());
    }

    public function testGetNavigation(): void
    {
        $expected = '<a href="/cms-user-guide/viewdoc/test-element">Test element</a>';
        $this->assertContains($expected, $this->controller->getNavigation());
    }

    public function testGetFileName(): void
    {
        $this->assertEquals('introduction.md', $this->controller->getFileName());

        $this->controller->setURLParams($this->params);
        $this->assertEquals('accessing-the-website.md', $this->controller->getFileName());
    }

    public function testGetContent(): void
    {
        $this->controller->setURLParams($this->params);

        $expected = '<h1>Accessing the website (test version)</h1>';
        $this->assertContains($expected, $this->controller->getContent());

        $expected = '<p>There are two environments; Production and UAT. Production is where the live website lives';
        $this->assertContains($expected, $this->controller->getContent());
    }

    public function testGetConvertedMD(): void
    {
        $this->controller->setURLParams($this->params);
        $filename = $this->controller->getFileName();

        $expected = '<h1>Accessing the website (test version)</h1>';
        $this->assertContains($expected, $this->controller->getConvertedMD($filename));

        $expected = '<p>There are two environments; Production and UAT. Production is where the live website lives';
        $this->assertContains($expected, $this->controller->getConvertedMD($filename));
    }

    public function testIndex(): void
    {
        $array = $this->controller->viewdoc();
        $this->assertArrayHasKey('Navigation', $array);
        $this->assertArrayHasKey('Content', $array);

        $expected = '<a href="/cms-user-guide/viewdoc/test-element">Test element</a>';
        $this->assertContains($expected, $array['Navigation']);

        $expected = '<h1>Introduction (for testing purposes)</h1>';
        $this->assertContains($expected, $array['Content']);
    }

    public function testViewdoc(): void
    {
        // set url params so we're actually loading a file
        $this->controller->setURLParams($this->params);

        $array = $this->controller->viewdoc();
        $this->assertArrayHasKey('Navigation', $array);
        $this->assertArrayHasKey('Content', $array);

        $expected = '<a href="/cms-user-guide/viewdoc/test-element">Test element</a>';
        $this->assertContains($expected, $array['Navigation']);

        $expected = '<h1>Accessing the website (test version)</h1>';
        $this->assertContains($expected, $array['Content']);
    }

}
