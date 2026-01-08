<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimeEntry extends Model
{
    protected $fillable = [
        'project_id',
        'user_id',
        'description',
        'started_at',
        'stopped_at',
        'resumed_at',
        'duration_minutes',
        'is_billable'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'stopped_at' => 'datetime',
        'resumed_at' => 'datetime',
        'is_billable' => 'boolean'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function invoiceItem()
    {
        return $this->hasOne(InvoiceItem::class);
    }
}
