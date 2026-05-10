<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pupil extends Model
{
    protected $fillable = ['code', 'name', 'dob', 'gender', 'active_status', 'dev_class_rate'];

    protected function casts(): array
    {
        return [
            'dob' => 'date',
            'active_status' => 'boolean',
        ];
    }

    public function classes()
    {
        return $this->belongsToMany(SchoolClass::class, 'class_pupil', 'pupil_id', 'class_id')
            ->withPivot('rate');
    }

    public function presences()
    {
        return $this->hasMany(PupilPresence::class);
    }
}
