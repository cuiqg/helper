<?php

/**
 * Str.php for helper.
 *               _
 *   _______  __(_)___ _____ _
 *  / ___/ / / / / __ `/ __ `/
 * / /__/ /_/ / / /_/ / /_/ /
 * \___/\__,_/_/\__, /\__, /
 *                /_//____/
 */

namespace Cuiqg\Helper;

use CachingIterator;
use Exception;

/**
 * 腾讯防水墙
 */
class TCaptcha
{
    protected $appid, $secret;

    public function __construct($config)
    {
        $this->appid = $config['appid'];
        $this->secret = $config['secret'];
    }

    public static function verify($ticket, $randstr, $user_ip = '0.0.0.0')
    {
        $url = 'https://ssl.captcha.qq.com/ticket/verify';

        $query = [
            'aid' => self::$appid,
            'AppSecretKey' => self::$secret,
            'Ticket' => $ticket,
            'Randstr' => $randstr,
            'UserIP' => $user_ip,
        ];

        try {
            $result = curl_request($url, $query, 'post');
        } catch (Exception $e) {
            throw $e;
        }

        $result = json_decode($result, true);

        if (json_last_error()) {
            throw new Exception(json_last_error_msg(), json_last_error());
        }

        if ($result['response'] != 1) {
            throw new Exception($result['err_msg'], $result['evil_level']);
        }

        return true;
    }
}
