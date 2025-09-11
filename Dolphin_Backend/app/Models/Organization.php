<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Organization extends Model
{
    use SoftDeletes;
    // Keep only fields that should remain on organizations table. Other contact/profile
    // information will be sourced from the owning user / user_details tables.
    protected $fillable = [
        'contract_start',
        'contract_end',
        'sales_person',
    'last_contacted',
        'certified_staff',
        'user_id',
        'org_name',
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

    // Fallback accessors: when organization-level fields like admin_email or
    // main_contact were removed, read them from the associated user/userDetails.
    public function getAdminEmailAttribute($value)
    {
        if (!empty($value)) {
            return $value;
        }
        if ($this->user) {
            return $this->user->email ?? null;
        }
        return null;
    }

    public function getAdminPhoneAttribute($value)
    {
        if (!empty($value)) {
            return $value;
        }
        if ($this->user && $this->user->userDetails) {
            return $this->user->userDetails->phone ?? null;
        }
        return null;
    }

    public function getOrgNameAttribute($value)
    {
        if (!empty($value)) {
            return $value;
        }
        if ($this->user && $this->user->userDetails) {
            return $this->user->userDetails->org_name ?? ($this->user->email ?? null);
        }
        return null;
    }

    public function getAddress1Attribute($value)
    {
    if (!empty($value)) {
            return $value;
        }
        return $this->user->userDetails->address ?? null;
    }

    public function getZipAttribute($value)
    {
        if (!empty($value)) {
            return $value;
        }
        return $this->user->userDetails->zip ?? null;
    }
}
