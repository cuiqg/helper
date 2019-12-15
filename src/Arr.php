<?php
/**
 * Arr.php for helper.
 *               _
 *   _______  __(_)___ _____ _
 *  / ___/ / / / / __ `/ __ `/
 * / /__/ /_/ / / /_/ / /_/ /
 * \___/\__,_/_/\__, /\__, /
 *                /_//____/
 */

namespace Cuiqg\Helper;


class Arr
{
    public static function first($array, callable $callback = null, $default = null)
    {
        if(is_null($callback)) {
            if(empty($array)) {
                return value($default);
            }

            foreach($array as $item) {
                return $item;
            }
        }

        foreach($array as $key => $value) {
            if($callback($value, $key)) {
                return $value;
            }
        }

        return value($default);
    }

    /**
     * 转为 XML
     * @param array $data
     * @return string
     */
    public static function toXml($data) {

        $xml = "<xml>";
        foreach($data as $key => $val) {
            if(is_numeric($val)) {
                $xml .= "<{$key}>{$val}</{$key}>";
            } else {
                $xml .= "<{$key}><![CDATA[{$val}]]></{$key}>";
            }
        }
        $xml.="</xml>";

        return $xml;
    }

    /**
     * 数组转查询字符串
     *
     * @param  array  $data
     * @return string
     */
    public static function arrayToQuery(array $data) {
        $query = '';
        foreach ($data as $key => $item) {
            $query .= '&'.$key.'='.$item;
        }
        return ltrim($query, '&');
    }
}
