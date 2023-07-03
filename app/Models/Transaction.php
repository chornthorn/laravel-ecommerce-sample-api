<?php

namespace App\Models;

use App\Models\Order;
use App\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'payment_method_id',
        'transaction_reference_id',
        'transaction_date',
        'transaction_amount',
        'status',
    ];

    /**
     * This method is called when the Transaction model is being booted.
     * It sets up a creating event listener that generates a random string with an integer
     * and assigns it to the transaction_reference_id attribute before creating a new Transaction.
     *
     * @return void
     */
    protected static function booted()
    {
        static::creating(function ($transaction) {
            // random string with integer : #f5c8a3e6
            $transaction->transaction_reference_id = '#' . substr(md5(rand()), 0, 8);
        });
    }

    /**
     * Get the order that owns the transaction.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the payment that owns the transaction.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    // status human readable
     public function getStatusAttribute($value)
    {
        $status = [
            0 => 'Pending',
            1 => 'Authorized',
            2 => 'Settled',
            3 => 'Declined',
            4 => 'Refunded',
            5 => 'Chargeback',
        ];

        return $status[$value];
    }

    // status match case for saving
     public function setStatusAttribute($value)
    {
        $status = [
            'pending' => 0,
            'authorized' => 1,
            'settled' => 2,
            'declined' => 3,
            'refunded' => 4,
            'chargeback' => 5,
        ];

        $this->attributes['status'] = $status[$value];
    }

    // scope for status
    public function scopeStatus($query, $status)
    {
        $statusIn = [
            'pending' => 0,
            'authorized' => 1,
            'settled' => 2,
            'declined' => 3,
            'refunded' => 4,
            'chargeback' => 5,
        ];

        return $query->where('status', $statusIn[$status]);
    }
}
