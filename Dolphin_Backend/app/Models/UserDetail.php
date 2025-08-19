<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDetail extends Model
{
    use HasFactory;


        protected $fillable = [
            'user_id',
            'first_name',
            'last_name',
            'email',
            'phone',
            'find_us',
            'org_name',
            'org_size',
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
