<?php

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Insert or update data into a table
 * @param array $data
 * @param string $table
 * @param bool $hasTimestamp
 * @return bool
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 */
function insertOrUpdateSql(array $data, string $table, bool $hasTimestamp = true)
{
    $now = Carbon::now('UTC')->toDateTimeString();
    $first = reset($data);
    $columns = array_keys($first);
    if ($hasTimestamp) {
        $columns[] = 'updated_at';
        $columns[] = 'created_at';
    }
    //compute columns
    $columnsStr = '`' . implode('`,`', $columns) . '`';

    //compute values
    $valuesStr = implode(',', array_map(function ($row) use ($hasTimestamp, $now) {
        if ($hasTimestamp) {
            $row[] = $now;
            $row[] = $now;
        }
        $row = array_map(function ($value) {
                return '"'.str_replace('"', '""', $value).'"';
        }, $row);
        return '('.implode(',', $row).')';
    }, $data));

    //compute updates
    if ($hasTimestamp) {
        array_pop($columns); //remove created_at;
    }
    $updates = array_map(function ($value) {
                return "$value = VALUES($value)";
    }, $columns);
    $updatesStr = implode(',', $updates);

    $sql = "INSERT INTO {$table}({$columnsStr}) VALUES {$valuesStr} ON DUPLICATE KEY UPDATE {$updatesStr}";
    return DB::statement($sql);
}
