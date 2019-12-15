<?php

namespace Cuiqg\Helper;


class Gravatar
{

    /**
     * @param string $email 邮箱
     * @param int $s 尺寸 [ 1 <= $s <= 2048 ]
     * @param string $d 默认图片 [ 404 | mp | identicon | monsterid | wavatar| retro | robohash ]
     * @param string $r 评级 [ g | pg | r | x ]
     * @return string
     */
    public static function image($email = '', $s = 80, $d = 'mp', $r = 'g') {
        $url = 'https://www.gravatar.com/avatar/';

        if(filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $url .= md5(strtolower(trim($email)));
        } else {
            $url .= str_repeat('0', 32);
        }

        $url .= '?'.http_build_query([
                's' => $s,
                'd' => $d,
                'r' => $r
            ]);

        return $url;
    }

    public static function profile($email) {
        $url = 'https://www.gravatar.com/';
        $url .= md5(strtolower(trim($email)));
        $url .= '.json';

        return curl_request($url);
    }
}
