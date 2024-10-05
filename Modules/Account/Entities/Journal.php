<?php

namespace Modules\Account\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Journal extends Model
{
    use HasFactory;

    protected $fillable = ['journal_id', 'invoice_id', 'account_id', 'debit', 'credit', 'description'];

    protected static function newFactory()
    {
        return \Modules\Accounts\Database\factories\JournalFactory::new();
    }
}
