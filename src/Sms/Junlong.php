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
        $this->password = strtoupper( md5( $config['password']));
        $this->extend = $config['extend'];
    }

    /**
     * 发送手机号
     *
     * @param string $mobile
     * @param string $content
     * @param  int  $level
     * @return bool|mixed
     * @throws \Exception
     */
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

    /**
     * 余额
     *
     * @return string
     * @throws \Exception
     */
    public function balance() {
        $url = 'http://hy.junlongtech.com:8087/hyWeb/getbalance';

        $params = [
            'username' => $this->username,
            'password' => $this->password,
        ];

        $result = curl_request( $url, $params, 'post');

        return $result;
    }

    /**
     * 格式化手机参数
     *
     * @param string $mobile
     * @return string
     */
    protected function formatMobile($mobile) {
        return is_array($mobile) ? implode(',', $mobile) : trim($mobile);
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

        return isset($errorArr[$key]) ? $errorArr[$key] : '为止错误';
    }
}