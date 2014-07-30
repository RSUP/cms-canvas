<?php namespace CmsCanvas\Content\Type\FieldType;

use View;
use CmsCanvas\Content\Type\FieldType;

class Text extends FieldType {

    /**
     * Returns a view of the text field input
     *
     * @return \Illuminate\View\View
     */
    public function inputField()
    {
        return View::make('CmsCanvas\Content\Type\FieldType::text.input')
            ->with('fieldType', $this);
    }

}