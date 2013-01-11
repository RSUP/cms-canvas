<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

class Text_field extends Field_type
{
    function settings()
    {
        $data = get_object_vars($this);
        
        return $this->load->view('settings/text', $data, TRUE);
    }

    function display_field()
    {
        $data = get_object_vars($this);
        
        return $this->load->view('text', $data, TRUE);
    }

    function output()
    {
        if ($this->is_inline_editable() && ( ! isset($this->Field->settings['inline_editing']) || $this->Field->settings['inline_editing']))
        {
            return '<div id="cc_field_' . $this->Entry->id . '_'. $this->Field->id  . '" class="cc_admin_editable cc_text_editable" contenteditable="true">{{ noparse }}' . $this->content . '{{ /noparse }}</div>';
        }
        else
        {
            return $this->content;
        }
    }
}
