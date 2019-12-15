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


class Str
{
    /**
     * 生成随机字符串
     *
     * @param int $length
     * @return string
     */
    public static function random($length = 16)
    {
        $string = '';
        while (($len = strlen($string)) < $length) {
            $size = $length - $len;

            $bytes = random_bytes($size);

            $string .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
        }

        return $string;
    }

    /**
     * 字符串转大写
     *
     * @param string $value
     * @return string
     */
    public static function upper($value)
    {
        return mb_strtoupper($value, 'UTF-8');
    }

    /**
     * 字符串转小写
     *
     * @param string $value
     * @return string
     */
    public static function lower($value)
    {
        return mb_strtolower($value, 'UTF-8');
    }

    /**
     * 字符串转标题格式
     *
     * @param string $value
     * @return string
     */
    public static function title($value)
    {
        return mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * 字符串截取
     *
     * @param string $value
     * @param int $limit
     * @param string $end
     * @return string
     */
    public static function limit($value, $limit = 100, $end = '...'){

        if(mb_strwidth($value, 'UTF-8') <= $limit) {
            return $value;
        }

        return rtrim(mb_strimwidth($value, 0, $limit, '', 'UTF-8')).$end;
    }

    /**
     * 返回字符串长度
     *
     * @param string $value
     * @param string $encoding
     * @return int
     */
    public static function length($value, $encoding = null)
    {
        if($encoding) {
            return mb_strlen($value, $encoding);
        }

        return mb_strlen($value);
    }

    /**
     * 返回部分字符串
     *
     * @param string $string
     * @param int $start
     * @param int|null $length
     * @return string
     */
    public static function substr($string, $start, $length = null)
    {
        return mb_substr($string, $start, $length, 'UTF-8');
    }

    /**
     * 隐藏手机号中间部分
     *
     * @param string $mobile
     * @param string $replacement
     * @return string
     */
    public static function concealMobile($mobile, $replacement = '^-^') {

        $replacement = " {$replacement} ";

        if( filter_var( $mobile, FILTER_VALIDATE_REGEXP, ['options' => ['regexp' => '/^1[2-9]\d{9}$/']])) {

           return substr_replace($mobile, $replacement, 3, 4);
        }

        return $mobile;
    }

    public static function queryToArray(string $query) {
        $queryArr = explode('&', $query);
        $params = [];
        
        foreach ($queryArr as $param) {
            $item = explode('=', $param);
            $params[$item[0]] = $item[1];
        }
        return $params;
    }
}