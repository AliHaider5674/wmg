<?php declare(strict_types=1);

namespace App\Printful\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class PrintfulLog
 * @package App\Printful\Model
 */
class PrintfulLog extends Model
{
    public $fillable = [
        'event_id',
        'event_output',
        'success',
    ];
}
