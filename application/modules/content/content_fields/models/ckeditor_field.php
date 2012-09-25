<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

class Ckeditor_field extends Field_type
{
    function view($data)
    {
        $this->template->add_package(array('ckeditor', 'ck_jq_adapter'));

        $config = "$(document).ready( function() {
            var config = { 
                toolbar : [
                                { name: 'styles', items : [ 'Styles','Format','Font','FontSize' ] },
                                { name: 'colors', items : [ 'TextColor','BGColor' ] },
                                { name: 'paragraph', items : [ 'NumberedList','BulletedList','-','Outdent','Indent','-','Blockquote','CreateDiv','- ','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock' ] },
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
                extraPlugins : 'stylesheetparser,mediaembed'," .
                (($this->settings->editor_stylesheet) ? "contentsCss : [ CKEDITOR.basePath + 'contents.css', '" . site_url(ADMIN_PATH . '/content/entries/css/' . $data['Entry']->id) . "?' + new Date().getTime()  ]," : "")
                 . "stylesSet : [],
                height : '300px',
                filebrowserBrowseUrl : '" . theme_url('/assets/js/kcfinder/browse.php?type=files') . "',
                filebrowserImageBrowseUrl : '" . theme_url('/assets/js/kcfinder/browse.php?type=images') . "',
                filebrowserFlashBrowseUrl : '" . theme_url('/assets/js/kcfinder/browse.php?type=flash') . "',
                filebrowserUploadUrl : '" . theme_url('/assets/js/kcfinder/upload.php?type=files') . "',
                filebrowserImageUploadUrl : '" . theme_url('/assets/js/kcfinder/upload.php?type=images') . "',
                filebrowserFlashUploadUrl : '" . theme_url('/assets/js/kcfinder/upload.php?type=flash') . "'
            };

            $('textarea.ckeditor_textarea').ckeditor(config);

            $('#entry_edit').submit( function() {
                $('div.textarea_content').each( function(index) {
                    $('textarea', this).val($('.editor', this).val());
                });
            });

        });";

        if ( ! in_array($config, $this->template->scripts))
        {
            $this->template->add_script($config);
        }

        return $this->load->view('ckeditor', $data, TRUE);
    }
}
