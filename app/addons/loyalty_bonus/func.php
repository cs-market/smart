<?php
/*****************************************************************************
 * This is a commercial software, only users who have purchased a  valid
 * license and accepts the terms of the License Agreement can install and use  
 * this program.
 *----------------------------------------------------------------------------
 * @copyright  LCC Alt-team: http://www.alt-team.com
 * @module     "Loyalty bonus" 
 * @version    4.x.x 
 * @license    http://www.alt-team.com/addons-license-agreement.html
 ****************************************************************************/

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_loyalty_bonus_get_user_info(&$user_id, &$get_profile, &$profile_id, &$user_data)
{
    $user_data['total_paid'] = 0;

    if (!empty($user_data['user_id'])) {
	
      $params['user_id'] = $user_data['user_id'];
      $condition = db_quote(' AND ?:orders.user_id IN (?n)', $params['user_id']);
      $paid_statuses = array('P', 'C');
      $totals = array (
            'totally_paid' => db_get_field("SELECT sum(t.total) FROM ( SELECT total FROM ?:orders  WHERE ?:orders.status IN (?a) $condition) as t", $paid_statuses),
        );      
        $user_data['total_paid'] = $totals['totally_paid'];
        if (empty($user_data['total_paid'])) {
            $user_data['total_paid'] = 0;
        }
    }
}

function fn_loyalty_bonus_user_init(&$auth, &$user_info)
{
    if (empty($auth['user_id']) || AREA != 'C') {
        return false;
    }
    
    $params['user_id'] = $auth['user_id'];
    $condition = db_quote(' AND ?:orders.user_id IN (?n)', $params['user_id']);
    $paid_statuses = array('P', 'C');
    $totals = array (
            'totally_paid' => db_get_field("SELECT sum(t.total) FROM ( SELECT total FROM ?:orders  WHERE ?:orders.status IN (?a) $condition) as t", $paid_statuses),
	); 
    $total_paid = $totals['totally_paid'];
    if (empty($total_paid)) {
        $total_paid = 0;
    }

    $auth['total_paid'] = $user_info['total_paid'] = $total_paid;

    return true;
}

