<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TutorSalary extends Model
{
    protected $fillable = ['tutor_id', 'salary'];

    protected function casts(): array
    {
        return [
            'salary' => 'decimal:2',
        ];
    }

    public function tutor()
    {
        return $this->belongsTo(Tutor::class);
    }
}
