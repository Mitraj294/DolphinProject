<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserRole extends Model
{
    use SoftDeletes;
    protected $table = 'user_roles';
    protected $fillable = ['user_id', 'role_id'];
    public $incrementing = false;
    protected $primaryKey = null;
    public $timestamps = true;
}
