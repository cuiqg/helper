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

class Oauth
{
    protected $appid;
    protected $secret;

    public function __construct($config)
    {
        $this->appid = $config['appid'];
        $this->secret = $config['secret'];
    }

    /**
     * 获取微信授权链接
     *
     * @param string $redirect_url 跳转链接
     * @param string $scope 授权方式 ['base' | 'userinfo']
     * @return String
     */
    public function getAuthUrl(string $redirect_url, string $state = '', string $scope = 'userinfo'): String
    {
        $allow_scope = [
            'base' => 'snsapi_base',
            'userinfo' => 'snsapi_userinfo'
        ];

        if (!Validator::url($redirect_url)) {
            throw new Exception('回传链接必须为URL');
        }

        if (!array_key_exists($scope, $allow_scope)) {
            throw new Exception('SCOPE 参数错误');
        }

        $url = 'https://open.weixin.qq.com/connect/oauth2/authorize';

        $query = [
            'appid'         => $this->appid,
            'redirect_uri'  => $redirect_url,
            'response_type' => 'code',
            'scope'         => $allow_scope[$scope],
            'state'         => $state,
        ];

        $url .= '?' . http_build_query($query) . '#wechat_redirect';

        return $url;
    }

    /**
     * 获取ACCESS TOKEN
     *
     * @param string $code
     * @return void
     */
    public function getToken(string $code)
    {
        $url = 'https://api.weixin.qq.com/sns/oauth2/access_token';

        $query = [
            'appid'      => $this->appid,
            'secret'     => $this->secret,
            'code'       => $code,
            'grant_type' => 'authorization_code'
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

    /**
     * 刷新ACCESS TOKEN
     *
     * @param string $refresh_token
     * @return void
     */
    public function refreshToken(string $refresh_token)
    {

        $url = 'https://api.weixin.qq.com/sns/oauth2/refresh_token';

        $query = [
            'appid'         => $this->appid,
            'grant_type'    => 'refresh_token',
            'refresh_token' => $refresh_token,
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

    /**
     * 检测用户ACCESS TOKEN
     *
     * @param string $access_token
     * @param string $openid
     * @return void
     */
    public function checkToken(string $access_token, string $openid)
    {
        $url = 'https://api.weixin.qq.com/sns/auth';

        $query = [
            'access_token' => $access_token,
            'openid' => $openid,
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

            return true;
        }

        throw new Exception($result['errmsg'], $result['errcode']);
    }

    /**
     * 获取用户信息
     *
     * @param string $access_token
     * @param string $openid
     * @return void
     */
    public function getUserinfo(string $access_token, string $openid)
    {
        $url = 'https://api.weixin.qq.com/sns/userinfo';

        $query = [
            'access_token' => $access_token,
            'openid' => $openid,
            'lang' => 'zh_CN',
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
