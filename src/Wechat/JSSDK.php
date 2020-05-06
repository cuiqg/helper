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

use Cuiqg\Helper\Arr;
use Cuiqg\Helper\Str;
use Cuiqg\Helper\Wechat\Util;
use Cuiqg\Helper\Validator;
use Exception;

class JSSDK
{
    protected $appid;
    protected $secret;

    public function __construct($config)
    {
        $this->appid = $config['appid'];
        $this->secret = $config['secret'];
    }

    /**
     * 获取ACCESS TOKEN
     */

    public function getAccessToken()
    {
        return Util::accessToken($this->appid, $this->secret);
    }

    /**
     * 获取 JS URL
     *
     * @return string
     */
    public function getScript(): string
    {
        return 'http://res.wx.qq.com/open/js/jweixin-1.6.0.js';
    }

    /**
     * 获取TICKET
     *
     * @param string $accessToken
     * @return void
     */
    public function getTicket(string $accessToken)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket';
        $query = [
            'access_token' => $accessToken,
            'type' => 'jsapi'
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

        if (isset($result['errcode']) && $result['errcode'] == 0) {

            return [
				"ticket" => $result['ticket'],
				"expires_in" => $result['expires_in']
			];
        }

        throw new Exception($result['errmsg'], $result['errcode']);
    }

    /**
     * 前端JS初始化
     *
     * @param string $ticket
     * @param string $url
     * @return void
     */
    public function getJsApi(string $ticket, string $url)
    {
        $params = [
            'noncestr' => Str::random(16),
            'jsapi_ticket' => $ticket,
            'timestamp' => time(),
            'url' => $url,
        ];

        ksort($params);

        $str = Arr::toQuery($params);

        $sign = sha1($str);

        return [
			'appId' => $this->appid,
			'timestamp' => $params['timestamp'],
			'nonceStr' => $params['noncestr'],
			'signature' => $sign,
		];
    }
}
