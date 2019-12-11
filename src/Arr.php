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
}