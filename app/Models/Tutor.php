<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tutor extends Model
{
    protected $fillable = ['name', 'telp', 'dob', 'domicille'];

    protected function casts(): array
    {
        return [
            'dob' => 'date',
        ];
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'tutor_subjects');
    }

    public function tutorSubjects()
    {
        return $this->hasMany(TutorSubject::class);
    }

    public function salaries()
    {
        return $this->hasMany(TutorSalary::class);
    }

    public function presences()
    {
        return $this->hasMany(TutorPresence::class);
    }
}
