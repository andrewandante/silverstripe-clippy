<?php

namespace SilverStripe\Clippy\Task;

use DOMDocument;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use RegexIterator;
use SilverStripe\Clippy\Controllers\DocumentationPageController;
use SilverStripe\Clippy\Converter\MarkdownConverter;
use SilverStripe\Clippy\PageTypes\DocumentationPage;
use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Path;
use SilverStripe\Dev\BuildTask;
use SilverStripe\Clippy\Model\UserGuide;
use SilverStripe\ORM\ValidationException;

class GenerateUserGuides extends BuildTask
{
    /**
     * @var string
     * @config
     */
    private static $segment = 'GenerateUserGuides';

    /**
     * @var string
     */
    protected $title = 'Creates record links to user guides';

    protected bool $verbose;

    /**
     * @param HTTPRequest $request
     * @throws ValidationException
     */
    public function run($request)
    {
        $this->verbose = (bool) $request->getVar('verbose');
        $count = 0;

        /** @var UserGuide $existingUserguide */
        foreach (UserGuide::get() as $existingUserguide) {
            $existingUserguide->delete();
        }

        $configDir = Config::inst()->get(DocumentationPageController::class, 'docs_dir');
        $guideDirectory = BASE_PATH . $configDir;
        // @TODO handle PDFs
        $allowedFileExtensions = Config::inst()->get(DocumentationPageController::class, 'allowed_extensions');

        if (!is_dir($guideDirectory)) {
            $this->log($configDir . ' does not exist - no user docs found');
            return;
        }

        // Find all .md files in the Guide Directory
        $directoryIterator = new RecursiveDirectoryIterator($guideDirectory);
        $iterator = new RecursiveIteratorIterator($directoryIterator);
        $files = new RegexIterator(
            $iterator,
            '/^.*\.(' . implode('|', $allowedFileExtensions) . ')$/i',
            RecursiveRegexIterator::GET_MATCH
        );

        foreach ($files as $file) {
            $fileType = pathinfo($file[0], PATHINFO_EXTENSION);

            // only support these types of files
            if (!in_array($fileType, $allowedFileExtensions)) {
                return;
            }

            $file = $file[0];

            $this->log('Creating guide for file: ' . $file);

            $guide = UserGuide::create();
            $guide->Title = basename($file);
            $guide->Type = $fileType;
            $guide->MarkdownPath = $file;
            // Attempt to derive class from directory structure a la templates
            $classCandidate = str_replace(
                DIRECTORY_SEPARATOR,
                '\\',
                substr($file, strlen($guideDirectory), -strlen('.md'))
            );

            if (ClassInfo::exists($classCandidate)) {
                $guide->DerivedClass = $classCandidate;
            }

            $htmlContent = "";
            $fileContents = file_get_contents($file);

            // @TODO use something like Injector::inst()->get(UserGuideMarkdownConverter::class) to allow
            // injection and configuration
            $converter = new MarkdownConverter();
            $markdown = $converter->convert($fileContents);

            $references = $markdown->getDocument()->getReferenceMap();
            if ($references->contains('ClassName')) {
                $classCandidate = $references->get('ClassName')->getTitle();
                if (ClassInfo::exists($classCandidate)) {
                    $guide->DerivedClass = $classCandidate;
                }
            }

            if ($references->contains('Title')) {
                $guide->Title = $references->get('Title')->getTitle();
            }

            if ($references->contains('Description')) {
                $guide->Description = $references->get('Description')->getTitle();
            }

            $htmlContent = $markdown->getContent();

            // transform any urls that do not have an https:// we can assume they are relative links
            $htmlDocument = new DOMDocument();
            $htmlDocument->loadHTML($htmlContent);
            $links = $htmlDocument->getElementsByTagName('a');
            $docPageSegment = DocumentationPage::config()->get('default_url_segment');
            $docPageUrl = Director::absoluteBaseURL() . $docPageSegment;

            foreach ($links as $link) {
                $linkHref = $link->getAttribute("href");
                if ($this->isAbsoluteLink($linkHref) || $this->isJumpToLink($linkHref)) {
                    continue;
                }
                $fullLinkPath = dirname($file) . DIRECTORY_SEPARATOR . $linkHref;
                $fullDocsDir = BASE_PATH . $configDir;
                $relativeLinkPath = substr($fullLinkPath, strlen($fullDocsDir));
                $finalPath = strpos($relativeLinkPath, '.md') !== false
                    ? substr($relativeLinkPath, 0, -strlen('.md'))
                    : $relativeLinkPath;
                $link->setAttribute(
                    'href',
                    Path::join(
                        DIRECTORY_SEPARATOR,
                        $docPageSegment,
                        'viewdoc',
                        $finalPath
                    )
                );
                $this->log('    > Changed relative link from: ' . $linkHref . ' to: ' . $link->getAttribute("href"));
            }

            $images = $htmlDocument->getElementsByTagName('img');
            foreach ($images as $image) {
                $imageSRC = $image->getAttribute('src');
                if ($this->isAbsoluteLink($imageSRC)) {
                    continue;
                }
                $fullImagePath = dirname($file) . DIRECTORY_SEPARATOR . $imageSRC;

                $image->setAttribute('src', $docPageUrl . '/streamInImage?imagePath=' . $fullImagePath);
                $this->log('    > Changed relative image src from: ' . $imageSRC . ' to: ' . $image->getAttribute("src"));
            }

            $htmlContent = $htmlDocument->saveHTML();
            $guide->Content = $htmlContent;

            $guide->write();
            ++$count;
            $this->log('    > ' . $guide->Title . ' was written');
            $this->log(' ');
        }
        $this->log($count . ' total guides written', true);
    }

    protected function log(string $message, bool $force = false): void
    {
        if ($this->verbose || $force) {
            echo $message . (Director::is_cli() ? PHP_EOL : '<br>');
        }
    }

    protected function isJumpToLink(string $linkHref): bool
    {
        return substr($linkHref, 0, 1) === '#';
    }

    protected function isAbsoluteLink(string $linkHref): bool
    {
        return strpos($linkHref, 'http://') === 0 || strpos($linkHref, 'https://') === 0;
    }
}
