<?php
/**
 * Junlong.php for helper.
 *               _
 *   _______  __(_)___ _____ _
 *  / ___/ / / / / __ `/ __ `/
 * / /__/ /_/ / / /_/ / /_/ /
 * \___/\__,_/_/\__, /\__, /
 *                /_//____/
 */

namespace Cuiqg\Helper\Sms;
use InvalidArgumentException;

class Junlong
{
    protected static $username;
    protected static $password;
    protected static $extend;

    public function __construct($config)
    {
        static::$username = $config['username'];
        static::$password = $config['password'];
        static::$extend = $config['extend'];
    }

    public static function send( $mobile, $content, $level = 1) {
        $url = 'http://hy.junlongtech.com:8086/getsms';
        $params = [
            'username' => static::$username,
            'password' => static::$password,
            'mobile' => static::formatMobile($mobile),
            'extend' => static::$extend,
            'level' => $level,
            'content' => $content,
        ];

        $result = curl_request( $url, $params, 'post');

        parse_str($result, $parseResult);

        if($parseResult['result'] == 0) {
            return $parseResult['msgid'];
        } else {
            return false;
        }
    }

    protected static function formatMobile($mobile) {
        return is_array($mobile) ? implode(',', $mobile) : trim($mobile);
    }
}