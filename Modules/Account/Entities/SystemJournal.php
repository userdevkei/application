<?php

namespace Modules\Account\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class SystemJournal extends Model
{
    use HasFactory, softDeletes;

    protected $keyType = 'string';
    protected $primaryKey = 'journal_id';
    protected $date = 'deleted_at';
    protected $fillable = ['journal_id', 'journal_name', 'effect', 'status'];

    protected static function newFactory()
    {
        return \Modules\Account\Database\factories\SystemJournalFactory::new();
    }
}
