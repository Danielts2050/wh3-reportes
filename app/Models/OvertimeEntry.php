<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OvertimeEntry extends Model
{
    protected $fillable = [
        'user_id',
        'employee_name',
        'employee_code',
        'hours',
        'work_date'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}