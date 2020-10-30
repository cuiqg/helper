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

use \Exception;
use mysqli;

class Sql
{

    /**
     * 分页
     *
     * @param mysqli $db
     * @param string $sql
     * @param integer $page
     * @param integer $size
     * @return void
     */
    public static function pager(mysqli $db, $sql, $page = 1, $size = 10)
    {

        $sql = preg_replace('/^select/i', 'select sql_calc_found_rows', $sql, 1);
        $page = $page <= 0 ? 1 : intval($page);
        $size = $size <= 0 ? 10 : intval($size);

        $offset = ($page - 1) * intval($size);

        $sql .= " " . "limit {$offset},{$size}";

        if (mysqli_connect_errno()) {
            throw new Exception(mysqli_connect_error(), -1);
        }

        mysqli_set_charset($db, 'utf8');

        $rows = mysqli_query($db, $sql);

        if (mysqli_errno($db)) {
            throw new Exception(mysqli_error($db), -1);
        }

        $rows_count = mysqli_num_rows($rows);

        $total = mysqli_query($db, 'select found_rows()');

        $total_count = current(mysqli_fetch_assoc($total));

        $total_pages = ceil($total_count / $size);

        $next = ($total_pages - $page) > 0 ? $page + 1 : 0;

        $prev = ($page - 1) > 0 ? $page - 1 : 0;

        $data = [];

        while ($row = mysqli_fetch_array($rows, MYSQLI_ASSOC)) {
            $data[] = $row;
        }

        mysqli_free_result($rows);

        //mysqli_close($db);

        return [
            'page' => $page,
            'size' => $size,
            'prev' => $prev,
            'next' => $next,
            'total_page' => (int)$total_pages,
            'total_count' => (int)$total_count,
            'current_count' => count($data),
            'rows' => $data,
        ];
    }
}
