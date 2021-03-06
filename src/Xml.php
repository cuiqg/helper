<?php

namespace Cuiqg\Helper;


class Xml
{
    /**
     * 转为 Array
     *
     * @param string $data
     * @return array
     */
    public static function toArray($data)
    {

        libxml_disable_entity_loader(TRUE);
        $arr = json_decode(json_encode(simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $arr;
    }
}
