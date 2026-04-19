<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassSession extends Model
{
    protected $fillable = ['number_of_pupils', 'date', 'class_id', 'material', 'photo_file'];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function tutorPresences()
    {
        return $this->hasMany(TutorPresence::class);
    }

    public function pupilPresences()
    {
        return $this->hasMany(PupilPresence::class);
    }
}
