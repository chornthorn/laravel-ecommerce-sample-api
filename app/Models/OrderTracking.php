<?php

namespace App\Models;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderTracking extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'status',
        // other fields...
    ];
    /**
     * Defines a relationship between the OrderTracking model and the Order model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // tranform: pending, 1: approved, 2: in_progress, 3: shipped, 4: cancelled, 5: refunded
    public function getStatusAttribute($value)
    {
        $status = [
            0 => 'Pending',
            1 => 'Approved',
            2 => 'In Progress',
            3 => 'Shipped',
            4 => 'Cancelled',
            5 => 'Refunded',
        ];

        return $status[$value];
    }

    // set tranform
    public function setStatusAttribute($value)
    {
        $status = [
            'pending' => 0,
            'approved' => 1,
            'in_progress' => 2,
            'shipped' => 3,
            'cancelled' => 4,
            'refunded' => 5,
        ];

        $this->attributes['status'] = $status[$value];
    }

    // status match case static
    public function scopeStatus($query, $status)
    {
        $statuss = [
            'pending' => 0,
            'approved' => 1,
            'in_progress' => 2,
            'shipped' => 3,
            'cancelled' => 4,
            'refunded' => 5,
        ];

        return $query->where('status', $statuss[$status]);
    }
    // to use: OrderTracking::status('pending')->get();
}
