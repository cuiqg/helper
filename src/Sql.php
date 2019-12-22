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
use mysqli;

class Sql
{

    public static function pager(mysqli $db, $sql, $page = 1, $size = 10) {

        $sql = preg_replace('/^select/i', 'select sql_calc_found_row', $sql, 1);

        $db->query($sql);

    }
}