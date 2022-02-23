<?php

namespace SilverStripe\Clippy\Task;

use DOMDocument;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use RegexIterator;
use SilverStripe\Clippy\Controllers\DocumentationPageController;
use SilverStripe\Control\Director;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\BuildTask;
use SilverStripe\Clippy\Model\UserGuide;

class GenerateUserGuides extends BuildTask
{
    private static $segment = 'GenerateUserGuides';

    protected $title = 'Creates record links to user guides';

    public function run($request)
    {

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

        $guides = UserGuide::get();

        foreach ($files as $file) {
            $fileType = pathinfo($file[0], PATHINFO_EXTENSION);

            // only support these types of files
            if (!in_array($fileType, $allowedFileExtensions)) {
                return;
            }

            $file = $file[0];
            $guide = $guides->find('MarkdownPath', $file);

            // @TODO make this work for non-markdown files
            if (is_null($guide)) {
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
            }

            $htmlContent = "";
            $fileContents = file_get_contents($file);
            if ($fileType === 'html') {
                $htmlContent = $fileContents;
            }

            if ($fileType === 'md') {
                // prototyping
                // Not sure if this is a good idea
                if (strstr($fileContents, '@UserDocs_Skip')) {
                    $fileContents = str_replace('@UserDocs_Skip=', '', $fileContents);
                    $this->log('@UserDocs_Skip found - skip ' . $file);
                    continue;
                }

                // @TODO - this only works if the annotation is at the bottom of the file, fix that
                $derivedClass = trim(
                    str_replace(
                        '@UserDocs_Class_Name=',
                        '',
                        strstr($fileContents, '@UserDocs_Class_Name=')
                    )
                );
                if ($derivedClass) {
                    if (ClassInfo::exists($derivedClass)) {
                        $guide->DerivedClass = $derivedClass;
                        $fileContents = str_replace('@UserDocs_Class_Name=' . $derivedClass, '', $fileContents);
                    }
                }

                // @TODO use something like Injector::inst()->get(UserGuideMarkdownConverter::class) to allow
                // injection and configuration
                $converter = new GithubFlavoredMarkdownConverter();
                $htmlContent = $converter->convert($fileContents)->getContent();
            }


            if ($fileType === 'md' || $fileType === 'html') {
                // transform any urls that do not have an https:// we can assume they are relative links
                $htmlDocument = new DOMDocument();
                $htmlDocument->loadHTML($htmlContent);
                $links = $htmlDocument->getElementsByTagName('a');
                $siteURL = Director::absoluteBaseURL();

                foreach ($links as $link) {
                    $linkHref = $link->getAttribute("href");

                    if ($this->isRelativeLink($linkHref) || !$this->isJumpToLink($linkHref)) {
                        $link->setAttribute('href', $siteURL . 'userguides?filePath=' . $linkHref);
                        $this->log('changed: ' . $linkHref . ' to: ' . $link->getAttribute("href"));
                    }
                }

                $images = $htmlDocument->getElementsByTagName('img');
                foreach ($images as $image) {
                    $imageSRC = $image->getAttribute("src");

                    if (str_contains($imageSRC, 'http') == false) {
                        $image->setAttribute('src', $siteURL . 'userguides?streamInImage=' . $imageSRC);
                        $this->log('changed: ' . $imageSRC . ' to: ' . $image->getAttribute("src"));
                    }
                }

                $htmlContent = $htmlDocument->saveHTML();
                $guide->Content = $htmlContent;
            }

            $guide->write();
            $this->log($file . ' was written');
        }
    }

    protected function log($message)
    {
        echo $message . (Director::is_cli() ? PHP_EOL : '<br>');
    }

    // We determine a relative link to either contain 'http'
    protected function isRelativeLink($linkHref)
    {
        return str_contains($linkHref, 'http') == false;
    }

    protected function isJumpToLink($linkHref)
    {
        return substr($linkHref, 0, 1) === '#';
    }
}
