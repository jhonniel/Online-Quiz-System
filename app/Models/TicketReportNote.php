<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketReportNote extends Model
{
    protected $fillable = [
        'ticket_report_id',
        'user_id',
        'body',
    ];

    public function ticket()
    {
        return $this->belongsTo(TicketReport::class, 'ticket_report_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

