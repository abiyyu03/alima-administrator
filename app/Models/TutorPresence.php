<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TutorPresence extends Model
{
    protected $fillable = ['class_session_id', 'tutor_id', 'status'];

    public function classSession()
    {
        return $this->belongsTo(ClassSession::class);
    }

    public function tutor()
    {
        return $this->belongsTo(Tutor::class);
    }
}
