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
    protected $username;
    protected $password;
    protected $extend;

    public function __construct($config)
    {
        $this->username = $config['username'];
        $this->password = $config['password'];
        $this->extend = $config['extend'];
    }

    public function send( $mobile, $content, $level = 1) {

        $url = 'http://hy.junlongtech.com:8086/getsms';

        $params = [
            'username' => $this->username,
            'password' => $this->password,
            'mobile' => $this->formatMobile($mobile),
            'extend' => $this->extend,
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

    protected function formatMobile($mobile) {
        return is_array($mobile) ? implode(',', $mobile) : trim($mobile);
    }
}