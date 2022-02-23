<?php

namespace SilverStripe\Clippy\Model;

use SilverStripe\ORM\DataObject;

class UserGuide extends DataObject
{
    private static $table_name = 'UserGuide';

    private static $db = [
        'Type' => 'Varchar',
        'Title' => 'Varchar',
        'PreNotes' => 'HTMLText',
        'Content' => 'HTMLText',
        'PostNotes' => 'HTMLText',
        'MarkdownPath' => 'Varchar', //@TODO update to FilePath as we are doing more than just .md now
        'DerivedClass' => 'Varchar',
    ];

    private static $summary_fields = [
        'Type' => 'File type',
        'Title' => 'File Name',
        'DerivedClass' => 'Class',
    ];

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName([
            'PreNotes',
            'PostNotes'
        ]);

        $fields->makeFieldReadonly([
            'Title',
            'Content',
            'MarkdownPath',
            'DerivedClass',
        ]);

        $fields->dataFieldByName('Content')->addExtraClass('img-max-width table-borders');

        return $fields;
    }
}
