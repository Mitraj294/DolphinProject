<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\MemberRole;

class Member extends Model
{
    use HasFactory, SoftDeletes, \Illuminate\Notifications\Notifiable;
    protected $fillable = ['first_name', 'last_name', 'email', 'phone', 'organization_id', 'user_id'];

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_member')->withTimestamps();
    }

    public function memberRoles()
    {
        return $this->belongsToMany(MemberRole::class, 'member_member_role');
    }

    /**
     * Helper to get the primary role name (compatibility with older code).
     */
    public function getMemberRoleAttribute()
    {
        $r = $this->memberRoles()->first();
        return $r ? $r->name : null;
    }

    /**
     * Helper to set member's roles from a string or array. Accepts either a single role name or an array of names/ids.
     */
    public function setMemberRoleAttribute($value)
    {
        // accept comma separated string as well
        if (is_string($value)) {
            $parts = array_filter(array_map('trim', explode(',', $value)));
        } elseif (is_array($value)) {
            $parts = $value;
        } else {
            $parts = [$value];
        }

        // normalize incoming parts: handle objects (stdClass), arrays, and numeric ids
        $roleIds = [];
        foreach ($parts as $p) {
            // If an array or object was sent (e.g. {id:1,name:'Manager'}), try to extract id or name
            if (is_object($p)) {
                // try common properties
                $pArr = (array) $p;
                $p = $pArr['id'] ?? $pArr['name'] ?? ($pArr[0] ?? null);
            } elseif (is_array($p)) {
                $p = $p['id'] ?? $p['name'] ?? ($p[0] ?? null);
            }

            if ($p === null || $p === '') continue;

            // if the value is numeric, treat as id
            if (is_numeric($p)) {
                $roleIds[] = (int) $p;
                continue;
            }

            // otherwise treat as role name string
            $name = trim((string) $p);
            if ($name === '') continue;
            $role = MemberRole::firstOrCreate(['name' => $name]);
            $roleIds[] = $role->id;
        }

        if ($roleIds) {
            $this->memberRoles()->sync($roleIds);
        }
    }
}
