<?php

namespace Modules\Account\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JournalSchedule extends Model
{
    use HasFactory;

    protected $fillable = ['journal_schedule_id', 'purchase_id', 'journal_id', 'duration', 'amount_due', 'monthly_due', 'status'];

    protected static function newFactory()
    {
        return \Modules\Account\Database\factories\JournalScheduleFactory::new();
    }
}
