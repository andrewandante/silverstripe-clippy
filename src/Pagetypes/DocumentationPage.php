<?php

namespace SilverStripe\Clippy\PageTypes;

use Page;
use SilverStripe\Clippy\Controllers\DocumentationPageController;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\ORM\DB;
use SilverStripe\Security\Member;
use SilverStripe\Versioned\Versioned;

/**
 * This page type allows for display of .md documentation files as html content within a web page.
 * Document located in the app/docs/ directory will be parsed and rendered within this page's template.
 */
class DocumentationPage extends Page
{

    private static string $table_name = 'DocumentationPage';

    private static string $icon_class = 'font-icon-book-open';

    private static string $controller_name = DocumentationPageController::class;

    private static string $allowed_children = 'none';

    private static array $defaults = [
        'ShowInMenus' => 0,
        'ShowInSearch' => 0,
    ];

    /**
     * Only allow DocumentationPage to be created if one does not already exist on the website
     *
     * @param Member $member
     * @param array $context
     * @return bool
     */
    public function canCreate($member = null, $context = array()): bool
    {
        $page = DocumentationPage::get()->first();

        return !$page; // If a documentation page doesn't currently exist, it can be created
    }

    /**
     * We don't want to allow CMS manipulation of data for this page type, as it is
     * intended only for display of .md docs
     *
     * @return FieldList
     */
    public function getCMSFields(): FieldList
    {
        $fields = parent::getCMSFields();
        $fields->removeFieldsFromTab(
            'Root.Main',
            [
                'URLSegment',
                'MenuTitle',
                'ElementalArea',
            ]
        );
        $fields->removeByName('Metadata');
        $fields->addFieldsToTab(
            'Root.Main',
            [
                ReadonlyField::create('URlSegment', 'URLSegment', $this->URLSegment),
                LiteralField::create(
                    'CMSUserGuideInfo',
                    '<div class="message notice">
                        <p><strong>DocumentationPage</strong></p>
                        <p>This page displays documentation for the bespoke features of this website,
                        and does not contain any editable content.</p>
                        <p>It is viewable only by logged-in users with CMS access.</p>
                      </div>'
                ),
                LiteralField::create(
                    'CMSUserGuideLink',
                    '<a href="'.$this->Link().'" class="btn btn-primary" target="_blank">View CMS User Guide</a>'
                ),
            ]
        );

        return $fields;
    }

    /**
     * We don't want to allow CMS manipulation of data for this page type, as it is
     * intended only for display of .md docs
     *
     * @return FieldList
     */
    public function getSettingsFields(): FieldList
    {
        $fields = parent::getSettingsFields();
        $fields->removeByName('Settings');
        $fields->addFieldToTab(
            'Root.Settings',
            LiteralField::create(
                'SettingsFieldsMessage',
                '<div class="message notice">
                    Settings are not configurable for this page type (<strong>DocumentationPage</strong>)
                </div>'
            )
        );

        return $fields;
    }

    /**
     * Add default record to database
     */
    public function requireDefaultRecords(): void
    {
        parent::requireDefaultRecords();

        // phpcs:ignore
        if (static::class == self::class && $this->config()->create_default_pages) {
            if (!DocumentationPage::get()->first()) {
                $DocumentationPage = new DocumentationPage();
                $DocumentationPage->Title = 'CMS User Guide';
                $DocumentationPage->Content = '';
                $DocumentationPage->write();
                $DocumentationPage->copyVersionToStage(Versioned::DRAFT, Versioned::LIVE);
                $DocumentationPage->flushCache();
                DB::alteration_message('Documentation Page created', 'created');
            }
        }
    }

}
