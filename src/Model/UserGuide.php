<?php

namespace SilverStripe\Clippy\Model;

use SilverStripe\Clippy\Controllers\DocumentationPageController;
use SilverStripe\Core\Config\Config;
use SilverStripe\ORM\DataObject;

class UserGuide extends DataObject
{
    private static string $table_name = 'UserGuide';

    private static string $singular_name = 'User Guide';

    private static array $db = [
        'Type' => 'Varchar',
        'Title' => 'Varchar',
        'Description' => 'Varchar',
        'Content' => 'HTMLText',
        'MarkdownPath' => 'Varchar', //@TODO update to FilePath as we are doing more than just .md now
        'DerivedClass' => 'Varchar',
    ];

    private static array $summary_fields = [
        'Title' => 'Title',
        'Description' => 'Description',
    ];

    public function getURLSegment(): string
    {
        $fullDocsDir = BASE_PATH . Config::inst()->get(DocumentationPageController::class, 'docs_dir');
        $relativePath = substr($this->MarkdownPath, strlen($fullDocsDir));

        return substr($relativePath, 0, -strlen('.md'));
    }
}
