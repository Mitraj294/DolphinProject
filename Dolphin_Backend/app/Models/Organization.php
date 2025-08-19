<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Organization extends Model
{
    protected $fillable = [
        'org_name',
        'size',
        'source',
        'address1',
        'address2',
        'zip',
        'country_id',
        'state_id',
        'city_id',
        'contract_start',
        'contract_end',
        'main_contact',
        'admin_email',
        'admin_phone',
        'sales_person',
        'last_contacted',
        'certified_staff',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Add relationship to get all users in the organization
    public function users()
    {
        return $this->hasMany(User::class, 'organization_id');
    }
}
