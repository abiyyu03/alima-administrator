<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolClass extends Model
{
    protected $table = 'classes';

    protected $fillable = ['name', 'grade_id', 'course_type_id'];

    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }

    public function courseType()
    {
        return $this->belongsTo(CourseType::class);
    }

    public function pupils()
    {
        return $this->hasMany(Pupil::class, 'class_id');
    }

    public function sessions()
    {
        return $this->hasMany(ClassSession::class, 'class_id');
    }
}
