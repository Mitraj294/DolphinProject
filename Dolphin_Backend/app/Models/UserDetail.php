<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserDetail extends Model
{
    use HasFactory;
    use SoftDeletes;


        protected $fillable = [
            'user_id',
            'phone',
            'find_us',
            'organization_name',
            'organization_size',
            'address',
            'country_id',
            'state_id',
            'city_id',
            'zip',
        ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }
}
