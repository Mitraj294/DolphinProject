<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use HasFactory , SoftDeletes;
    protected $fillable = [
        'first_name', 'last_name', 'email', 'phone', 'find_us', 'org_name', 'org_size', 'address', 'country_id', 'state_id', 'city_id', 'zip', 'notes'
    ];
}
