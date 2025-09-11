<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use App\Models\Traits\HasRoles;
class User extends Authenticatable
{

    use HasApiTokens, HasFactory, SoftDeletes, Notifiable, HasRoles;


    protected $fillable = [
        'id',
        'first_name',
        'last_name',
        'email',
        'password',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    // Eloquent relationships
    public function userDetails()
    {
        return $this->hasOne(UserDetail::class, 'user_id');
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'user_id');
    }

    // Organization owned by this user (owner)
    public function organization()
    {
        return $this->hasOne(Organization::class, 'user_id');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id')->withTimestamps();
    }

    /**
     * Check if the user has a specific role.
     */
    public function hasRole(string $roleName): bool
    {
        return $this->roles()->where('name', $roleName)->exists();
    }

    /**
     * Convenience: is the user a superadmin?
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('superadmin');
    }

        public function users()
    {
        return $this->belongsToMany(User::class, 'user_role');
    }


    
}