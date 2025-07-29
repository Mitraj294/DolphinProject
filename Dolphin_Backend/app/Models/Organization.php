<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'size',
        'source',
        'address1',
        'address2',
        'city',
        'state',
        'zip',
        'country',
        'contract_start',
        'contract_end',
        'main_contact',
        'admin_email',
        'admin_phone',
        'sales_person',
        'last_contacted',
        'certified_staff',
    ];
}