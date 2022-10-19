<?php declare(strict_types=1);

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Shipment Model
 */
class Shipment extends Model
{
    protected $guarded = ['id'];

    public function items()
    {
        return $this->hasMany(ShipmentItem::class, 'parent_id', 'id');
    }
}
