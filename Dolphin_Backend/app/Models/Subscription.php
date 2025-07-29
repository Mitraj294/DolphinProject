<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = [
        'user_id',
        'stripe_subscription_id',
        'stripe_customer_id',
        'plan',
        'status',
        'payment_method',
        'payment_date',
        'subscription_start',
        'subscription_end',
        'amount',
        'receipt_url',
        'invoice_number',
        'description',
        'customer_name',
        'customer_email',
        'customer_country',
        'meta',    
    ];
  protected $casts = [
        'payment_date' => 'datetime',
        'subscription_start' => 'datetime',
        'subscription_end' => 'datetime',
        'meta' => 'array', 
    ];
 
}
