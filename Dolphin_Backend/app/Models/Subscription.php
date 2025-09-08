<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subscription extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'user_id',
        'stripe_subscription_id',
        'stripe_customer_id',
        'plan',
        'status',
        'payment_method',
        'default_payment_method_id',
        'payment_method_type',
        'payment_method_brand',
        'payment_method_last4',
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
