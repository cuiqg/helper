<?php

if (!function_exists('is_windows')) {
    /**
     * 检测WINDOWS系统
     *
     * @return bool
     */
    function is_windows()
    {
        return PHP_OS_FAMILY === 'Windows';
    }
}

if (!function_exists('is_wechat')) {
    /**
     * 检测微信浏览器
     *
     * @return bool
     */
    function is_wechat()
    {
        if (preg_match('/MicroMessenger/i', $_SERVER['HTTP_USER_AGENT'])) {
            return true;
        }
        return false;
    }
}

if (!function_exists('is_alipay')) {
    /**
     * 检测支付宝浏览器
     *
     * @return bool
     */
    function is_alipay()
    {
        if (preg_match('/Alipay/i', $_SERVER['HTTP_USER_AGENT'])) {
            return true;
        }
        return false;
    }
}

if (!function_exists('ip_info')) {

    /**
     * IP 信息
     * @param string $ip
     * @return array
     * @throws Exception
     */
    function ip_info($ip)
    {
        $ip = trim($ip);

        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new Exception('IP无效');
        }

        $url = 'http://freeapi.ipip.net/' . $ip;

        $res = curl_request($url, [], 'get');
        $res = json_decode($res, true);

        return [
            'country'  => $res[0],
            'province' => $res[1],
            'city'     => $res[2],
        ];
    }
}


if (!function_exists('self_url')) {

    /**
     * 获取浏览器访问链接
     *
     * @return void
     */
    function self_url()
    {
        $server_name = isset($_SERVER['HTTP_X_ORIGINAL_HOST']) ? $_SERVER['HTTP_X_ORIGINAL_HOST'] : $_SERVER['SERVER_NAME'];

        if ($_SERVER['SERVER_PORT'] == 80) {

            $url = 'http://' . $server_name . $_SERVER['REQUEST_URI'];
        } elseif ($_SERVER['SERVER_PORT'] == 443) {

            $url = 'https://' . $server_name . $_SERVER['REQUEST_URI'];
        } else {

            $url = 'http://' . $server_name . ':' . $_SERVER['SERVER_PORT'] . $_SERVER['REQUEST_URI'];
        }

        return $url;
    }
}

if (!function_exists('curl_request')) {
    /**
     * 进行网络请求
     *
     * @param  string  $url
     * @param  string  $method
     * @param  array  $params
     * @param  array  $header
     * @param  array  $ssl
     * @param  int  $timeout
     * @return bool|string
     * @throws Exception
     */
    function curl_request($url, $params = [], $method = 'get', $header = [], $ssl = [], $timeout = 30)
    {
        $ch = curl_init();

        if (strtolower($method) == 'get' && !empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        $opts = [
            CURLOPT_URL            => $url,
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_HEADER         => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT      => 'CUIQG/HELPER',
            CURLOPT_FOLLOWLOCATION => true,
        ];

        if (!empty($header)) {
            $opts[CURLOPT_HTTPHEADER] = $header;
        }

        if (!empty($ssl)) {
            if (isset($ssl['cert'])) {
                $opts[CURLOPT_SSLCERTTYPE] = 'PEM';
                $opts[CURLOPT_SSLCERT] = $ssl['cert'];
            }

            if (isset($ssl['key'])) {
                $opts[CURLOPT_SSLKEYTYPE] = 'PEM';
                $opts[CURLOPT_SSLKEY] = $ssl['key'];
            }
        }

        if (strtolower($method) == 'post') {
            $opts[CURLOPT_POST] = true;
            $opts[CURLOPT_POSTFIELDS] = $params;
        }

        curl_setopt_array($ch, $opts);

        $res = curl_exec($ch);
        $error = curl_error($ch);
        $errno = curl_errno($ch);
        curl_close($ch);

        if ($errno) {
            throw new Exception($error, $errno);
        }

        return $res;
    }
}
