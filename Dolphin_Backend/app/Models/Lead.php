<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use HasFactory , SoftDeletes;
    protected $fillable = [
    'first_name', 'last_name', 'email', 'phone', 'find_us', 'organization_name', 'organization_size', 'address', 'country_id', 'state_id', 'city_id', 'zip', 'notes', 'status', 'created_by', 'sales_person_id'
    ];
    
    protected $casts = [
        'assessment_sent_at' => 'datetime',
        'registered_at' => 'datetime',
    ];

    public function salesPerson()
    {
        return $this->belongsTo(User::class, 'sales_person_id');
    }
}
