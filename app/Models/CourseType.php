<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseType extends Model
{
    protected $fillable = ['name'];

    public function classes()
    {
        return $this->hasMany(SchoolClass::class, 'course_type_id');
    }
}
