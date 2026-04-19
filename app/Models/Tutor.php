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

    public function classes()
    {
        return $this->belongsToMany(SchoolClass::class, 'tutor_classes', 'tutor_id', 'class_id')
                    ->withPivot('amount')
                    ->withTimestamps();
    }

    public function tutorClasses()
    {
        return $this->hasMany(TutorClass::class);
    }

    public function presences()
    {
        return $this->hasMany(TutorPresence::class);
    }

    public function user()
    {
        return $this->hasOne(User::class);
    }
}
