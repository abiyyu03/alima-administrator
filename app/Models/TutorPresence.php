<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TutorPresence extends Model
{
    protected $fillable = ['class_session_id', 'tutor_id', 'status', 'amount', 'rate', 'note'];

    protected function casts(): array
    {
        return [
            'tutor_id'         => 'integer',
            'class_session_id' => 'integer',
        ];
    }

    public function getEarnedAttribute(): float
    {
        return in_array($this->status, ['absence', 'absent', 'permission', 'sick'])
            ? 0
            : (float) $this->amount;
    }

    public function classSession()
    {
        return $this->belongsTo(ClassSession::class);
    }

    public function tutor()
    {
        return $this->belongsTo(Tutor::class);
    }

    public function salary()
    {
        return $this->hasOne(TutorSalary::class);
    }
}
