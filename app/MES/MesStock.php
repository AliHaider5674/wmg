<?php

namespace App\MES;

use Illuminate\Database\Eloquent\Model;

/**
 * MesStock
 *
 * Reports status of stock file import
 *
 * @category App\MES
 * @package  App\MES
 * @author   Dinesh Haria <dinesh.haria@warnermusic.com>
 * @license  WMG License
 * @link     http://www.wmg.com
 */
class MesStock extends Model
{
    const STATUS_PROCESSING = 0;
    const STATUS_PROCESSED  = 1;
    const STATUS_ERROR = 2;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'file', 'status', 'source_count', 'sku_count'
    ];
}
