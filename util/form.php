<?php

class Util_Form
{

    /** form display functions **/
    public static function get_form_inputs($section)
    {
        $func = "_get_form_inputs_$section";
        return self::$func();
    }

    private static function _get_form_inputs_user()
    {
        /* text boxes */
        foreach (array(
            'login' => 'Login',
            'display_name' => 'Display Name',
        ) as $name => $display_name) {
            $inputs[$name]['th'] = $display_name;
            $inputs[$name]['input'] = 'text';
            $inputs[$name]['value'] = '';
            $inputs[$name]['name'] = $name;
        }

        /* selects */
        foreach (array(
            'roles' => 'Roles'
        ) as $name => $display_name) {
            $inputs[$name]['th'] = $display_name;
            $inputs[$name]['input'] = 'select';
            $inputs[$name]['value'] = '';
            $inputs[$name]['options'] = array();
            $inputs[$name]['name'] = $name.'[]';
            $inputs[$name]['is_multiple'] = true;
        }

        $inputs['email']['th'] = 'Email';
        $inputs['email']['input'] = 'email';
        $inputs['email']['value'] = '';
        $inputs['email']['name'] = 'email';

        return array('inputs' => $inputs);
    }

    private static function _get_form_inputs_article()
    {
        /* text boxes */
        foreach (array(
            'title' => 'Title',
            'friendly_url' => 'Friendly URL',
        ) as $name => $display_name) {
            $inputs[$name]['th'] = $display_name;
            $inputs[$name]['input'] = 'text';
            $inputs[$name]['value'] = '';
            $inputs[$name]['name'] = $name;
        }
        $inputs['friendly_url']['attributes'] = array('disabled');

        /* selects */
        foreach (array(
            'tags' => 'Tags',
            'status' => 'Status',
        ) as $name => $display_name) {
            $inputs[$name]['th'] = $display_name;
            $inputs[$name]['input'] = 'select';
            $inputs[$name]['value'] = '';
            $inputs[$name]['options'] = array();
            $inputs[$name]['name'] = $name;
        }
        $inputs['tags']['is_multiple'] = true;
        $inputs['tags']['has_placeholder'] = true;
        $inputs['tags']['name'] = 'tag_ids[]';

        $inputs['status']['value'] = 'pending';
        $inputs['status']['has_placeholder'] = false;
        $inputs['status']['options'] = array(
            'pending' => array('name' => 'Pending'),
            'published' => array('name' => 'Published'),
            'archived' => array('name' => 'Trash'),
        );

        /* text areas */
        foreach (array('content' => 'Content') as $col => $name) {
            $inputs[$col]['th'] = $name;
            $inputs[$col]['input'] = 'textarea';
            $inputs[$col]['value'] = '';
            $inputs[$col]['name'] = $col;
        }

        // set all input fields to be not required initially
        foreach ($inputs as $input_name => $attrs) {
            $attrs['required'] = false;
        }

        return array('inputs' => $inputs);

    }

    public static function spit_out_form_input($inputs, $key, $input_id = null, $additional_class = "")
    {
        if (!isset($inputs[$key])) {
            return false;
        }
        $css_input_id = '';
        if (null !== $input_id) {
            $css_input_id = ' id="' . $input_id . '"';
        }
        $info = $inputs[$key];
        $output = '';
        $required_text = isset($info['required']) && true === $info['required'] ? ' required ' : '';
        switch ($info['input']) {
            case 'textarea':
                $output .= '<textarea class="form-control" autocomplete="off" name="' . $info['name'] . '" ' . $css_input_id . ' ' . $required_text . '    rows="3" >' . $info['value'] . '</textarea>';
                break;
            case 'select':
                $txt_is_multiple = '';
                if (isset($info['is_multiple']) && $info['is_multiple'] === true) {
                    $txt_is_multiple = ' multiple="multiple" ';
                }
                $output .= '<select class="form-control ' . $additional_class . ' "  ' . $txt_is_multiple . ' autocomplete="off" name="' . $info['name'] . '" ' . $css_input_id . ' ' . $required_text . '>';
                if (isset($info['has_placeholder']) && $info['has_placeholder'] === true) {
                    $output .= '<option value="">Please select...</option>';
                }
                foreach ($info['options'] as $id => $option) {
                    $selected = '';
                    if (is_array($info['value']) && in_array($id, $info['value']) ||
                        isset($info['value']) && $info['value'] == $id) {
                        $selected = 'selected="selected"';
                    }
                    $output .= '<option value="' . $id . '" ';
                    if (isset($option['attributes'])) {
                        $output .= implode(' ', $option['attributes']);
                    }
                    if (isset($option['disabled'])) {
                        $output .= ' disabled ';
                    }
                    $output .= ' ' . $selected . '>' . (isset($option['name']) ? $option['name'] : '') . '</option>';
                }
                $output .= '</select>';
                break;
            case 'text':
            case 'email':
                if (isset($info['is_multiple']) && $info['is_multiple'] === true) {
                    $output .= "<ul $css_input_id>";
                    foreach ($info['value'] as $value) {
                        $output .= "<li>";
                        $output .= '<input class="form-control" autocomplete="off" type="'.$info['input'].'" name="' . $info['name'] . '" ';
                        if (isset($info['attributes'])) {
                            $output .= implode(' ', $info['attributes']);
                        }
                        $output .= ' value="' . $value . '" />';
                        $output .= "</li>";
                    }
                    $output .= "</ul>";
                } else {
                    $output .= '<input class="form-control" autocomplete="off" type="'.$info['input'].'" name="' . $info['name'] . '" ';
                    if (isset($info['attributes'])) {
                        $output .= implode(' ', $info['attributes']);
                    }
                    $output .= ' value="' . $info['value'] . '"  ' . $css_input_id . ' ' . $required_text . '/>';
                }
                break;
            case 'file':
                $output .= '<input type="hidden" name="' . $info['name'] . '" value="' . $info['value'] . '" />';
                $output .= '<input class="form-control-file" autocomplete="off" type="file" name="' . $info['name'] . '" ';
                if (isset($info['attributes'])) {
                    $output .= implode(' ', $info['attributes']);
                }
                $output .= 'value="' . $info['value'] . '"  ' . $css_input_id . ' ' . $required_text . '/>&nbsp;&nbsp;&nbsp;';
                break;
            case 'radio':
                foreach ($info['options'] as $id => $option) {
                    $selected = $info['value'] == $id ? 'checked="checked"' : '';
                    $output .= '<input class="form-control" autocomplete="off" type="radio" name="' . $info['name'] . '" value="' . $id . '" ' . $selected;
                    $output .= ' ' . $css_input_id . '/>' . $option . '&nbsp;&nbsp;&nbsp';
                }
                break;
            case 'hidden':
                $output .= '<input type="hidden" name="' . $info['name'] . '" ';
                if (isset($info['attributes'])) {
                    $output .= implode(' ', $info['attributes']);
                }
                $output .= ' value="' . $info['value'] . '"  />';
            default:
                $output .= $info['value'];
                break;
        }
        return $output;
    }

    public static function spit_out_input_label($inputs, $key, $class = "")
    {
        if (empty($inputs[$key]['th'])) {
            return '';
        }
        $required = isset($inputs[$key]['required']) && true === $inputs[$key]['required'] ? '<span class="required">*</span>' : '';
        $classattr = !empty($class) ? " class='$class' " : "";
        return "<label for='{$inputs[$key]['name']}' $classattr >{$inputs[$key]['th']} $required</label>";
    }

}
