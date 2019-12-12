<?php

namespace Cuiqg\Helper;

/**
 * 密码生成/解析
 *
 * @package  Password
 */
class Password
{

    public static function make($value)
    {
        return password_hash($value, self::algorithm());
    }

    public static function check($value, $hashedValue) {
        return password_verify($value, $hashedValue);
    }

    public static function needsRehash($hashedValue)
    {
        return password_needs_rehash($hashedValue, self::algorithm());
    }

    public static function info($hashedValue)
    {
        return password_get_info($hashedValue);
    }

    protected static function algorithm()
    {
        return PASSWORD_DEFAULT;
    }
}
