<?php

if (!is_callable('fn_array_splice_assoc')) {
    function fn_array_splice_assoc(&$input, $offset, $length, $replacement = array()) {
        $replacement = (array) $replacement;
        $key_indices = array_flip(array_keys($input));
        if (isset($input[$offset]) && is_string($offset)) {
                $offset = $key_indices[$offset];
        }
        if (isset($input[$length]) && is_string($length)) {
                $length = $key_indices[$length] - $offset;
        }

        $input = array_slice($input, 0, $offset, TRUE)
                + $replacement
                + array_slice($input, $offset + $length, NULL, TRUE); 
    }
}

$schema = array_merge(['manager' => array(
    'label' => 'manager',
    'type' => 'manager_selectbox',
    'name' => 'manager',
)], $schema);

fn_array_splice_assoc($schema, -4, 0, [    'show_manager' => array(
    'label' => 'sales_plan.show_manager',
    'type' => 'checkbox',
    'name' => 'show_manager',
    'class' => 'clearfix',
)]);

return $schema;
