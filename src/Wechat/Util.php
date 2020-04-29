<?php

/**
 * Oauth.php for helper.
 *               _
 *   _______  __(_)___ _____ _
 *  / ___/ / / / / __ `/ __ `/
 * / /__/ /_/ / / /_/ / /_/ /
 * \___/\__,_/_/\__, /\__, /
 *                /_//____/
 */

namespace Cuiqg\Helper\Wechat;

use Cuiqg\Helper\Validator;
use Exception;

class Util
{

    /**
     * 获取基础ACCESS TOKEN
     *
     * @param string $appid
     * @param string $secret
     * @return void
     */
    public static function accessToken(string $appid, string $secret)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/token';

        $query = [
            'grant_type' => 'client_credential',
            'appid' => $appid,
            'secret' => $secret,
        ];

        try {
            $result = curl_request($url, $query, 'get');
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }

        $result = json_decode($result, true);

        if (json_last_error()) {
            throw new Exception(json_last_error_msg(), json_last_error());
        }

        if (isset($result['errcode'])) {
            throw new Exception($result['errmsg'], $result['errcode']);
        }

        return $result;
    }
}
