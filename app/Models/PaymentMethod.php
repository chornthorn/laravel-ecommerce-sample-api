<?php

namespace App\Models;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    /**
     * Defines a one-to-one relationship between PaymentMethod and Transaction models.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function transaction()
    {
        return $this->hasOne(Transaction::class);
    }

    // tranform boolean in response
    protected $casts = [
        'enabled' => 'boolean',
        'default' => 'boolean',
    ];
}
