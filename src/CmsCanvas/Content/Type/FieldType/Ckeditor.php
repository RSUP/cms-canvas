<?php namespace CmsCanvas\Content\Type\FieldType;

use View, Theme, Admin;
use CmsCanvas\Content\Type\FieldType;

class Ckeditor extends FieldType {

    /**
     * Returns a view of additional settings for the ckeditor field
     *
     * @return \Illuminate\View\View
     */
    public function settings()
    {
        return View::make('CmsCanvas\Content\Type\FieldType::ckeditor.settings')
            ->with('fieldType', $this);
    }

    /**
     * Returns an array of validation rules for the setting fields
     *
     * @return array
     */
    public function getSettingsValidationRules()
    {
        return array(
            'settings.height' => 'integer|min:0' 
        );
    }

    /**
     * Returns a view of the ckeditor field input
     *
     * @return \Illuminate\View\View
     */
    public function inputField()
    {
        Theme::addPackage('ckeditor');

        $config = "$(document).ready( function() {
            var ckeditor_config = { 
                toolbar : [
                    { name: 'styles', items : [ 'Styles','Format','Font','FontSize' ] },
                    { name: 'colors', items : [ 'TextColor','BGColor' ] },
                    { name: 'paragraph', items : [ 'NumberedList','BulletedList','-','Outdent','Indent','-','Blockquote','CreateDiv','- ','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock' ] },
                    { name: 'tools', items : [ 'ShowBlocks' ] },
                    { name: 'tools', items : [ 'Maximize' ] },
                                    '/',
                    { name: 'basicstyles', items : [ 'Bold','Italic','Underline','Subscript','Superscript','Strike','-','RemoveFormat' ] },
                    { name: 'clipboard', items : [ 'Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo' ] },
                    { name: 'editing', items : [ 'Find','Replace','-','Scayt' ] },
                    { name: 'insert', items : [ 'Image','Flash','MediaEmbed','Table','HorizontalRule','SpecialChar','Iframe' ] },
                    { name: 'links', items : [ 'Link','Unlink','Anchor' ] },
                    { name: 'document', items : [ 'Source' ] }
                ],
                entities : true,
                extraPlugins : 'stylesheetparser',
                contentsCss : ['" .Theme::asset('js/ckeditor/contents.css')."'".(( ! empty($this->entry->id)) ? ", '".Admin::url('content/entry/'.$this->entry->id.'/css')."?' + new Date().getTime()" : '')."],
                stylesSet : [],
                height : '300px',
                filebrowserBrowseUrl : '".Theme::asset('js/kcfinder/browse.php?type=files') . "',
                filebrowserImageBrowseUrl : '".Theme::asset('js/kcfinder/browse.php?type=images') . "',
                filebrowserFlashBrowseUrl : '".Theme::asset('js/kcfinder/browse.php?type=flash') . "',
                filebrowserUploadUrl : '".Theme::asset('js/kcfinder/upload.php?type=files') . "',
                filebrowserImageUploadUrl : '".Theme::asset('js/kcfinder/upload.php?type=images') . "',
                filebrowserFlashUploadUrl : '".Theme::asset('js/kcfinder/upload.php?type=flash') . "'
            };

            $('textarea.ckeditor_textarea').each(function(index) {
                ckeditor_config.height = $(this).height();
                CKEDITOR.replace($(this).attr('name'), ckeditor_config); 
            });

        });";

        Theme::addInlineScript($config, true);

        return View::make('CmsCanvas\Content\Type\FieldType::ckeditor.input')
            ->with('fieldType', $this);
    }

    /**
     * Returns the rendered data for the field
     *
     * @return string
     */
    public function render()
    {
        if ( ! isset($this->field->settings->inline_editing) || $this->field->settingsinline_editing)
        {
            // return $this->inlineEditable();
            return $this->data;
        }
        else
        {
            return $this->data;
        }
    }

    /**
     * Returns the inline editable field
     * @todo - Need to get this working
     *
     * @return string
     */
    private function inlineEditable()
    {
        if ($this->isInlineEditable())
        {
            Theme::addJavascript(Admin::asset('js/ckeditor_inline_editable.js'));
            $_SESSION['KCFINDER'] = array();
            $_SESSION['KCFINDER']['disabled'] = false;
            $_SESSION['isLoggedIn'] = true;

            return '<div id="cc_field_' . $this->Entry->id . '_'. $this->Field->id  . '" class="cc_admin_editable cc_ck_editable" contenteditable="true">{{ noparse }}' . $this->data . '{{ /noparse }}</div>';
        }
    }

}