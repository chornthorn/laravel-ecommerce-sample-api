<?php

namespace App\Models;

use App\Models\Billing;
use App\Models\OrderTracking;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    /**
     * Get the customer that owns the order.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function customer()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the products that belong to the order.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'order_items')
                    ->withPivot('quantity', 'price')
                    ->withTimestamps();
    }

    /**
     * Get the transactions associated with the order.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
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


    /**
     * Get the order trackings associated with the order.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orderTrackings()
    {
        return $this->hasMany(OrderTracking::class);
    }

    // can refund if order status is not 5, 4, 0: refunded, cancelled, pending
    public function scopeCanRefund($query)
    {
        return $query->whereNotIn('status', [5, 4, 0]);
    }

    // can cancel if order status is not cancelled, refunded
    public function scopeCanCancel($query)
    {
        return $query->whereNotIn('status', [4, 5]);
    }

    // can reorder if order status is shipped,refunded,cancelled
    public function scopeCanReorder($query)
    {
        return $query->whereIn('status', [3, 5, 4]);
    }

    // with order items
    public function scopeWithOrderItems($query)
    {
        return $query->with('products');
    }

    // Billing
    public function billing()
    {
        return $this->hasOne(Billing::class);
    }
}
