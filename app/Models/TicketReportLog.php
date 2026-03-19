<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketReportLog extends Model
{
    protected $fillable = [
        'ticket_report_id',
        'user_id',
        'action',
        'from_status',
        'to_status',
        'meta',
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

