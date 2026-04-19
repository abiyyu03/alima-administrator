<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = ['name', 'grade_id'];

    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }

    public function tutors()
    {
        return $this->belongsToMany(Tutor::class, 'tutor_subjects');
    }

    public function tutorSubjects()
    {
        return $this->hasMany(TutorSubject::class);
    }
}
