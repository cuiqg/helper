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

use Cuiqg\Helper\Validator;
use Exception;

class Junlong
{
    protected $username;
    protected $password;
    protected $extend;

    public function __construct($config)
    {
        $this->username = $config['username'];
        $this->password = strtoupper( md5( $config['password']));
        $this->extend = $config['extend'];
    }

    /**
     * 发送手机号
     *
     * @param string $mobile
     * @param string $content
     * @return bool|mixed
     * @throws \Exception
     */
    public function send( $mobile, $content) {

        $url = 'http://hy.junlongtech.com:8086/getsms';

        $params = [
            'username' => $this->username,
            'password' => $this->password,
            'mobile' => $this->formatMobile($mobile),
            'content' => $content,
            'extend' => $this->extend,
        ];

        $result = curl_request( $url, $params, 'get');

        parse_str($result, $parseResult);

        if($parseResult['result'] == 0) {
            return $parseResult['msgId'];
        } else {
            throw new Exception($this->parseError($parseResult['result']), -1);
        }
    }

    /**
     * 格式化手机参数
     *
     * @param string $mobile
     * @return string
     */
    private function formatMobile($mobile) {
       if(is_array($mobile)) {

            $result = array_unique($mobile);
            $result = array_filter($result, function($val){
                return Validator::mobile($val);
            });
            return implode(',', $result);

       } else {
           return trim($mobile);
       }
    }

    /**
     * 解析错误
     *
     * @param string $key
     * @return string
     */
    private function parseError( $key) {
        $errorArr = [
            '-100' => '参数错误',
            '-101' => '帐号和密码验证失败或是帐号被注销',
            '-102' => '手机号码为空或含有不合法的手机号码',
            '-103' => '内容为空或含有非法字符',
            '-104' => '账号余额不足',
            '-105' => '扩展码错误',
            '-106' => '产品错误',
            '-107' => '查询频繁，超过查询频率',
            '-108' => '查询状态无量',
            '-109' => 'IP验证错',
            '-110' => '其他错误'
        ];

        return isset($errorArr[$key]) ? $errorArr[$key] : '未知错误';
    }
}