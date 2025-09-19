<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Country;
use App\Models\State;
use App\Models\City;

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
    public function state()
    {
        return $this->belongsTo(State::class, 'state_id');
    }
    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }
}
