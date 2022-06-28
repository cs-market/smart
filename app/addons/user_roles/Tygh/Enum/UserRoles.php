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

    public static function getList() {
        $roles = [
            self::CUSTOMER => 'customer'
        ];

        fn_set_hook('user_roles_get_list', $roles);

        return $roles;
    }

    public static function __callStatic($name, $arguments) {
        $name = fn_uncamelize($name);
        if (strpos($name, 'is_') !== false) {
            $check_type = str_replace('is_', '', $name);
            if (isset(self::getList()[$arguments[0]])) {
                return self::getList()[$arguments[0]] === $check_type;
            } elseif (is_callable('fn_user_roles_' . $name)) {
                return call_user_func('fn_user_roles_' . $name, $arguments[0]);
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}
