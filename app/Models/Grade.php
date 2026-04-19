<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    protected $fillable = ['name'];

    public function classes()
    {
        return $this->hasMany(SchoolClass::class, 'grade_id');
    }

    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }
}
