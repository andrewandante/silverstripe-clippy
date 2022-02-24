<?php

namespace SilverStripe\Clippy\Model;

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

}
