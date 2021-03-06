<?php

/**
 * Validator.php for helper.
 *               _
 *   _______  __(_)___ _____ _
 *  / ___/ / / / / __ `/ __ `/
 * / /__/ /_/ / / /_/ / /_/ /
 * \___/\__,_/_/\__, /\__, /
 *                /_//____/
 */

namespace Cuiqg\Helper;


class Validator
{
    /**
     * 检测手机号
     *
     * @param string $string
     * @return bool
     */
    public static function mobile(string $string)
    {
        return filter_var($string, FILTER_VALIDATE_REGEXP, ['options' => [
            'regexp' => '/^1[3-9]\d{9}$/'
        ]]);
    }

    /**
     * 检测邮箱
     *
     * @param string $string
     * @return bool
     */
    public static function email(string $string)
    {
        return filter_var($string, FILTER_VALIDATE_EMAIL);
    }

    /**
     * 检测中文
     * @param string $string
     */
    public static function chinese(string $string)
    {

        return filter_var($string, FILTER_VALIDATE_REGEXP, ['options' => [
            'regexp' => '/^\p{Han}+$/u'
        ]]);
    }

    /**
     * 链接检测
     *
     * @param string $url
     * @return void
     */
    public static function url(string $url)
    {

        return filter_var($url, FILTER_VALIDATE_URL);
    }
}
