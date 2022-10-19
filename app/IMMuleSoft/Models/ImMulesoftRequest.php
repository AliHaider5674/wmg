<?php

namespace App\IMMuleSoft\Models;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * Class ImMulesoftRequest
 * @property int|mixed $status
 * @property mixed $attempts
 * @package App\IMMuleSoft\Models
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2022
 * @link     http://www.wmg.com
 */
class ImMulesoftRequest extends Model
{
    use HasFactory;

    public const STATUS_RECEIVED    = 0;
    public const STATUS_PROCESSING  = 1;
    public const STATUS_COMPLETE    = 2;
    public const STATUS_ERROR       = 3;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * @param QueryBuilder|EloquentBuilder $query
     * @param array $statuses
     * @return EloquentBuilder
     */
    public function scopeHasStatusIn($query, array $statuses): EloquentBuilder
    {
        return $query->whereIn('status', $statuses);
    }

    /**
     * @param QueryBuilder|EloquentBuilder $query
     * @param int $attempts
     * @return EloquentBuilder
     */
    public function scopeAttempts($query, int $attempts): EloquentBuilder
    {
        return $query->where('attempts', '<=', $attempts);
    }

    public function scopeResourceType($query, string $resourceType) : EloquentBuilder
    {
        return $query->where('resource_type', '=', $resourceType);
    }
}
