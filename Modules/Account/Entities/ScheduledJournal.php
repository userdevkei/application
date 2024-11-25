<?php

namespace Modules\Account\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScheduledJournal extends Model
{
    use HasFactory, softDeletes;

    protected $keyType = 'string';
    protected $primaryKey = 'scheduled_journal_id';
    protected $date = 'deleted_at';

    protected $fillable = ['scheduled_journal_id', 'journal_schedule_id', 'amount_settled' , 'date_settled'];

    protected static function newFactory()
    {
        return \Modules\Account\Database\factories\ScheduledJournalFactory::new();
    }
}
