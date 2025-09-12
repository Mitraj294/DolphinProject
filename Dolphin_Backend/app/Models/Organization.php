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
         'organization_name',
    'organization_size',
        'contract_start',
        'contract_end',
 
    'sales_person_id',
    'last_contacted',
        'certified_staff',
        'user_id',
   
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function salesPersonUser()
    {
        return $this->belongsTo(User::class, 'sales_person_id');
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
            return $this->user->userDetails->organization_name ?? ($this->user->email ?? null);
        }
        return null;
    }

    public function getOrganizationSizeAttribute($value)
    {
        if (!empty($value)) {
            return $value;
        }

        if ($this->user && $this->user->userDetails) {
            $ud = $this->user->userDetails->organization_size ?? null;
            if (!empty($ud)) {
                return $ud;
            }
        }

        // legacy column 'size' support
        if (!empty($this->size)) {
            return $this->size;
        }

        return null;
    }

    public function getAddress1Attribute($value)
    {
        if (!empty($value)) {
            return $value;
        }
        if ($this->user && $this->user->userDetails) {
            return $this->user->userDetails->address ?? null;
        }
        return null;
    }

    public function getZipAttribute($value)
    {
        if (!empty($value)) {
            return $value;
        }
        if ($this->user && $this->user->userDetails) {
            return $this->user->userDetails->zip ?? null;
        }
        return null;
    }
}
