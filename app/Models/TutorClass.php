<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TutorClass extends Model
{
    protected $table = 'tutor_classes';

    protected $fillable = ['tutor_id', 'class_id', 'amount'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2'];
    }

    public function tutor()
    {
        return $this->belongsTo(Tutor::class);
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }
}
