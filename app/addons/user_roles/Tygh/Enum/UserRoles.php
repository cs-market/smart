<?php

namespace Tygh\Enum;

/**
 *  UserRoles contains possible values for `users`.`user_role` DB field.
 *
 * @package Tygh\Enum
 */
class UserRoles
{
    const CUSTOMER = 'C';

    /**
     * @param string $user_role User role
     *
     * @return bool
     */
    public static function isCustomer($user_role)
    {
        return $user_role === self::CUSTOMER;
    }

    public static function getList($user_type = '') {
        $roles = [
            self::CUSTOMER => 'customer'
        ];

        fn_set_hook('user_roles_get_list', $roles, $user_type);

        return $roles;
    }

    public static function __callStatic($name, $arguments) {
        $name = fn_uncamelize($name);
        $params = isset($arguments[0]) ? $arguments[0] : '';
        if (strpos($name, 'is_') !== false) {
            $check_type = str_replace('is_', '', $name);
            if (isset(self::getList()[$params])) {
                return self::getList()[$params] === $check_type;
            } elseif (is_callable('fn_user_roles_' . $name)) {
                return call_user_func('fn_user_roles_' . $name, $params);
            } else {
                return false;
            }
        } elseif (isset(array_flip(self::getList())[$name])) {
            return array_flip(self::getList())[$name];
        } else {
            return false;
        }
    }
}
