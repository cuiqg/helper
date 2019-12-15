<?php

if ( ! function_exists('windows_os')) {
    /**
     * 检测WINDOWS系统
     *
     * @return bool
     */
    function windows_os()
    {
        return PHP_OS_FAMILY === 'Windows';
    }
}

if ( ! function_exists('curl_request')) {
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

        if (strcasecmp($method, 'get') == 0 && ! empty($params)) {
            $url .= '?'.http_build_query($params);
        }

        $opts = [
            CURLOPT_URL            => $url,
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_HEADER         => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_NONE,
            CURLOPT_USERAGENT      => 'CUIQG/HELPER',
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_DNS_SERVERS    => '180.76.76.76',
        ];

        if ( ! empty($header)) {
            $opts[CURLOPT_HTTPHEADER] = $header;
        }

        if ( ! empty($ssl)) {
            if (isset($ssl['cert'])) {
                $opts[CURLOPT_SSLCERT] = $ssl['cert'];
            }

            if (isset($ssl['key'])) {
                $opts[CURLOPT_SSLKEY] = $ssl['key'];
            }
        }

        if (strcasecmp($method, 'post')) {
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
