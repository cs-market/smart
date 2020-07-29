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

$schema['conditions']['total_paid'] = array (
    'operators' => array ('lte', 'gte', 'lt', 'gt'),
    'type' => 'input',
    'field' => '@auth.total_paid',
    'zones' => array('catalog', 'cart')
);

return $schema;
