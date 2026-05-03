<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pupil extends Model
{
    protected $fillable = ['code', 'name', 'dob', 'gender', 'class_id', 'active_status', 'dev_class_rate'];

    protected function casts(): array
    {
        return [
            'dob' => 'date',
            'active_status' => 'boolean',
        ];
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function presences()
    {
        return $this->hasMany(PupilPresence::class);
    }
}
