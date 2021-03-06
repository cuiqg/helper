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

use Cuiqg\Helper\Str;
use Cuiqg\Helper\Arr;
use Cuiqg\Helper\Xml;
use Exception;

class Pay
{
    protected $api_url;
    protected $appid;
    protected $client_secret;
    protected $client_key;
    protected $mchid;
    protected $key;
    protected $prepay_id;

    public function __construct($config)
    {
        $this->appid = $config['appid'];
        $this->mchid = $config['mchid'];
        $this->key = $config['key'];


        $this->api_url = 'https://api.mch.weixin.qq.com/';


        if (isset($config['client_secret'])) {
            $this->client_secret = $config['client_secret'];
        }

        if (isset($config['client_key'])) {
            $this->client_key = $config['client_key'];
        }

        date_default_timezone_set('PRC');
    }

    /**
     * 统一下单
     *
     * @param array ['order_no', 'body','total_fee', 'ip', 'notify_url', 'trade_type']
     * @return void
     */
    public function unfinOrder(array $data)
    {
        $url = $this->api_url . 'pay/unifiedorder';

        $params = [
            'appid'            => $this->appid,
            'mch_id'           => $this->mchid,
            'nonce_str'        => Str::random(16),
            'sign_type'        => 'MD5',
            'body'             => $data['body'],
            'out_trade_no'     => $data['order_no'],
            'fee_type'         => 'CNY',
            'total_fee'        => bcmul($data['total_fee'], 100, 0),
            'spbill_create_ip' => $data['ip'],
            'notify_url'       => $data['notify_url'],
            'trade_type'       => strtoupper($data['trade_type']),
            'scene_info'       => json_encode($data['scene_info'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        ];

        if (isset($data['attach'])) {
            $params['attach'] = $data['attach'];
        }

        if (isset($data['time_start'])) {
            $params['time_start'] = $data['time_start'];
        }

        if (isset($data['time_expire'])) {
            $params['time_expire'] = $data['time_expire'];
        }

        if (strtolower($data['trade_type']) == 'jsapi') {
            $params['openid'] = $data['openid'];
        }

        ksort($params);
        $params['sign'] = self::sign($params);

        $data = Arr::toXml($params);

        try {
            $result = curl_request($url, $data, 'post');
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }

        $result = Xml::toArray($result);

        if ($result['return_code'] != 'SUCCESS') {
            throw new Exception($result['return_msg']);
        }

        if ($this->sign($result) != $result['sign']) {
            throw new Exception('返回数据签名错误');
        }

        if ($result['result_code'] != 'SUCCESS') {
            throw new Exception($result['err_code_des']);
        }

        $res = [
            'trade_type' => $result['trade_type'],
            'prepay_id'  => $result['prepay_id'],
        ];

        $this->prepay_id = $result['prepay_id'];

        if (isset($result['code_url'])) {
            $res['code_url'] = $result['code_url'];
        }
        
        if (isset($result['mweb_url'])) {
            $res['mweb_url'] = $result['mweb_url'];
        }
        
        return $res;
    }

    /**
     * 关闭订单
     *
     * @param array ['order_no']
     * @return void
     */
    public function closeOrder(array $data)
    {
        $url = $this->api_url . 'pay/closeorder';

        $params = [
            'appid'        => $this->appid,
            'mch_id'       => $this->mchid,
            'out_trade_no' => $data['order_no'],
            'nonce_str'    => Str::random(16),
            'sign_type'    => 'MD5',
        ];

        ksort($params);
        $params['sign'] = self::sign($params);

        $data = Arr::toXml($params);
        try {
            $result = curl_request($url, $data, 'post');
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }

        $result = Xml::toArray($result);

        if ($result['return_code'] != 'SUCCESS') {
            throw new Exception($result['return_msg']);
        }

        if ($this->sign($result) != $result['sign']) {
            throw new Exception('返回数据签名错误');
        }

        if ($result['result_code'] != 'SUCCESS') {
            throw new Exception($result['err_code_des']);
        }

        return true;
    }

    /**
     * 退款
     *
     * @param array ['order_no', 'refund_no','total_fee', 'refund_fee', 'notify_url']
     * @return void
     */
    public function refund(array $data)
    {
        $url = $this->api_url . 'secapi/pay/refund';

        $params = [
            'appid'           => $this->appid,
            'mch_id'          => $this->mchid,
            'out_trade_no'    => $data['order_no'],
            'out_refund_no'   => $data['refund_no'],
            'nonce_str'       => Str::random(16),
            'sign_type'       => 'MD5',
            'total_fee'       => bcmul($data['total_fee'], 100, 0),
            'refund_fee'      => bcmul($data['refund_fee'], 100, 0),
            'refund_fee_type' => 'CNY',
            'refund_account'  => 'REFUND_SOURCE_UNSETTLED_FUNDS',
            'notify_url'      => $data['notify_url'],
        ];

        ksort($params);
        $params['sign'] = self::sign($params);

        $data = Arr::toXml($params);

        try {
            $result = curl_request($url, $data, 'POST', [], [
                'cert' => $this->client_secret,
                'key' => $this->client_key
            ]);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }

        $result = Xml::toArray($result);


        if ($result['return_code'] != 'SUCCESS') {
            throw new Exception($result['return_msg']);
        }

        if ($this->sign($result) != $result['sign']) {
            throw new Exception('返回数据签名错误');
        }

        if ($result['result_code'] != 'SUCCESS') {
            throw new Exception($result['err_code_des']);
        }

        return [
            'transaction_id' => $result['transaction_id'],
            'out_trade_no'   => $result['out_trade_no'],
            'out_refund_no'  => $result['out_refund_no'],
            'refund_id'      => $result['refund_id'],
            'refund_fee'     => $result['refund_fee'],
            'total_fee'      => $result['total_fee'],
            'cash_fee'       => bcdiv($result['cash_fee'], 100, 2),
        ];
    }

    /**
     * 二维码链接转成短链接
     *
     * @param string $url [weixin://wxpay/s/XXXXXX]
     * @return void
     */
    public function shortUrl(string $url)
    {
        $url = $this->api_url . 'tools/shorturl';

        $params = [
            'appid'     => $this->appid,
            'mch_id'    => $this->mchid,
            'long_url'  => $url,
            'nonce_str' => Str::random(16),
            'sign_type' => 'MD5',
        ];
        ksort($params);
        $params['sign'] = self::sign($params);
        $params['long_url'] = urlencode($url);

        $data = Arr::toXml($params);

        try {
            $result = curl_request($url, $data, 'post');
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }

        $result = Xml::toArray($result);

        if ($result['return_code'] != 'SUCCESS') {
            throw new Exception($result['return_msg']);
        }

        if ($this->sign($result) != $result['sign']) {
            throw new Exception('返回数据签名错误');
        }

        if ($result['result_code'] != 'SUCCESS') {
            throw new Exception($result['err_code_des']);
        }

        return $result['short_url'];
    }

    /**
     * 生成签名
     *
     * @param array $data
     * @return void
     */
    public function sign(array $data)
    {
        $buff = '';
        ksort($data);
        foreach ($data as $key => $item) {
            if ($key != 'sign' && $item != '' && !is_array($item)) {
                $buff .= $key . '=' . $item . '&';
            }
        }

        $buff .= 'key=' . $this->key;

        return strtoupper(md5($buff));
    }


    /**
     * JSAPI 参数
     *
     * @param string $prepay_id 预支付订单号
     * @return void
     */
    public function jsapi(string $prepay_id)
    {
        $data = [
            'appId'     => $this->appid,
            'timeStamp' => (string)time(),
            'nonceStr'  => (string)Str::random(16),
            'package'   => 'prepay_id=' . $prepay_id,
            'signType'  => "MD5",
        ];

        $data['paySign'] = self::sign($data);

        return $data;
    }
}
