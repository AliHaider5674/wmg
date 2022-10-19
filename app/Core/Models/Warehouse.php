<?php declare(strict_types=1);

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Warehouses Model
 * @class Warehouses
 */
class Warehouse extends Model
{
    protected $fillable = [
        'code',
        'name',
        'status',
    ];
}
