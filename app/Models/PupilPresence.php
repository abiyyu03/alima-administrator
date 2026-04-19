<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PupilPresence extends Model
{
    protected $fillable = ['class_session_id', 'pupil_id', 'status', 'note'];

    public function classSession()
    {
        return $this->belongsTo(ClassSession::class);
    }

    public function pupil()
    {
        return $this->belongsTo(Pupil::class);
    }
}
