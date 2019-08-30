<?php
/***************************************************************************
*                                                                          *
*   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
*                                                                          *
* This  is  commercial  software,  only  users  who have purchased a valid *
* license  and  accept  to the terms of the  License Agreement can install *
* and use this program.                                                    *
*                                                                          *
****************************************************************************
* PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
* "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
****************************************************************************/

use Tygh\Registry;

defined('BOOTSTRAP') or die('Access denied');

include_once Registry::get('config.dir.addons') . 'advanced_import/schemas/advanced_import/modifiers.functions.php';

$schema = array(
    'operations' => array(
        'sum' => array( // sum of two operands, e.g. sum($value, 1) => $value + 1
            'current'    => '$value', // operand name that refers to the field that the modifier is being applied to
            'parameters' => 2, // quantity of allowed parameters
            'operation'  => function ($a = 0, $b = 0) {
                return number_format((float) $a + (float) $b, 2, '.', '');
            },
        ),
        'sub' => array( // subtract the second operand from the first, e.g. sub($value, 1) => $value - 1
            'current'    => '$value',
            'parameters' => 2,
            'operation'  => function ($a = 0, $b = 0) {
                return number_format((float) $a - (float) $b, 2, '.', '');
            },
        ),
        'mul' => array( // multiplication of two operands, mul($value, 2) => $value * 2
            'current'    => '$value',
            'parameters' => 2,
            'operation'  => function ($a = 0, $b = 0) {
                return number_format((float) $a * (float) $b, 2, '.', '');
            },
        ),
        'div' => array( // divide the first operand by the second, div($value, 2) => $value / 2
            'current'    => '$value',
            'parameters' => 2,
            'operation'  => function ($a = 0, $b = 0) {
                $b = (float) $b;

                if ($b != 0) {
                    return number_format((float) $a / $b, 2, '.', '');
                }

                return $a;
            },
        ),
        'concat' => array( // concatenates provided strings
            'current'    => '$value',
            'parameters' => null, // any number of parameters
            'operation'  => function () {
                $arguments = array_map('strval', func_get_args());
                return implode('', $arguments);
            },
        ),
        'replace' => array( // replaces part of string
            'current'    => '$value',
            'parameters' => 3,
            'operation'  => function ($search = '', $replace = '', $subject = '') {
                return str_replace((string) $search, (string) $replace, (string) $subject);
            },
        ),
        'w_replace' => array( // replaces using wildcards
            'current'    => '$value',
            'parameters' => 3,
            'operation'  => function ($search = '', $replace = '', $subject = '') {
                $search = preg_quote($search, '@');
                $search = str_replace(array('\?', '\*'), array('.', '.+?\b'), $search);
                $pattern = implode('', array('@', $search, '@'));

                if ($search) {
                    return preg_replace($pattern, $replace, $subject);
                }

                return $subject;
            },
        ),
        'rand' => array( // generates a random number
            'current'    => null, // no reference to current filed's value
            'parameters' => 1,
            'operation'  => function ($max_value = 1000) {
                $max_value = (int) $max_value;
                return rand(1, $max_value ? $max_value : 1000);
            },
        ),
        'if' => array( // an if statement if(predicate, val1, val2) => if "predicate" evaluates to true "val1" will be returned "val2" otherwise
            'current'    => '$value',
            'parameters' => 3,
            'operation'  => function ($predicate, $val1, $val2) {
                return fn_advance_import_evaluate_predicate_expression($predicate)
                    ? $val1
                    : $val2;
            },
        ),
        'case' => array( // a case statement (predicate1, val1, predicate2, val2, ..., predicateN, valN) => will return the next value (argument) after the first predicate evaluated to true
            'current'    => '$value',
            'parameters' => null,
            'operation'  => function () {
                $result = '';
                $arguments = array_map('strval', func_get_args());

                if (count($arguments) % 2 !== 0) { // even number of arguments expected
                    return $result;
                }

                while (count($arguments)) {
                    $predicate = array_shift($arguments);
                    $value = array_shift($arguments);

                    if (fn_advance_import_evaluate_predicate_expression($predicate)) {
                        $result = $value;
                        break;
                    }
                }

                return $result;
            },
        ),
    ),
);

return $schema;