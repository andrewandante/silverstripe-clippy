<?php

namespace SilverStripe\Clippy\GridField;

use SilverStripe\Control\Controller;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\GridField\GridField_ActionMenuItem;
use SilverStripe\Forms\GridField\GridField_ActionMenuLink;
use SilverStripe\Forms\GridField\GridField_ColumnProvider;
use SilverStripe\Clippy\Controllers\CMSUserGuideController;
use SilverStripe\Model\ArrayData;
use SilverStripe\View\SSViewer;

/**
 * A button that allows a user to view readonly details of a record. This is
 * disabled by default and intended for use in readonly {@link GridField}
 * instances.
 */
class UserGuideViewButton implements GridField_ColumnProvider, GridField_ActionMenuLink
{
    /**
     * @inheritdoc
     */
    public function getTitle($gridField, $record, $columnName)
    {
        return _t(__CLASS__ . '.VIEW', "View");
    }

    /**
     * @inheritdoc
     */
    public function getGroup($gridField, $record, $columnName)
    {
        return GridField_ActionMenuItem::DEFAULT_GROUP;
    }

    /**
     * @inheritdoc
     */
    public function getExtraData($gridField, $record, $columnName)
    {
        return [
            "classNames" => "font-icon-eye action-detail view-link"
        ];
    }

    /**
     * @inheritdoc
     */
    public function getUrl($gridField, $record, $columnName)
    {
        return Controller::join_links($this->formatURL($gridField->Link('item'), $record));
    }

    public function augmentColumns($field, &$columns)
    {
        if (!in_array('Actions', $columns)) {
            $columns[] = 'Actions';
        }
    }

    public function getColumnsHandled($field)
    {
        return ['Actions'];
    }

    public function getColumnContent($field, $record, $col)
    {
        if (!$record->canView()) {
            return null;
        }
        $data = new ArrayData([
            'Link' => Controller::join_links($this->formatURL($field, $record))
        ]);
        $template = SSViewer::get_templates_by_class($this, '', __CLASS__);
        return $data->renderWith($template);
    }

    public function getColumnAttributes($field, $record, $col)
    {
        return ['class' => 'grid-field__col-compact'];
    }

    public function getColumnMetadata($gridField, $col)
    {
        return ['title' => null];
    }

    private function formatURL($field, $record)
    {
        $link = Injector::inst()->get(CMSUserGuideController::class)->Link();
        $controllerParams = Controller::curr()->getURLParams();

        return Controller::join_links(
            $link,
            $controllerParams['Action'],
            $controllerParams['ID'],
            '?ugid=' . $record->ID
        );
    }
}
