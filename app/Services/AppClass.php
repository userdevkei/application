<?php

namespace App\Services;

use App\Models\Station;
use Illuminate\Support\Facades\DB;
use Modules\Account\Entities\Journal;
use Modules\Account\Entities\JournalSchedule;
use Modules\Account\Entities\ScheduledJournal;

class AppClass
{
    public function clientsWithStock($id)
    {
        $locationId = Station::where('station_id', auth()->user()->station_id)->first()->location_id;
        $stations = Station::where('location_id', $locationId)->pluck('station_id')->toArray();

        $clients = DB::table('currentstock')->select('client_name', 'client_id')
            ->whereNotNull('current_stock')
            ->where('current_stock', '>', 0)
            ->whereNotNull('current_weight')
            ->where('current_weight', '>', 0)
            ->whereIn('station_id', $stations)
            ->where( 'current_stock', '>', 0)
            ->orderBy('client_name')
            ->get()
            ->groupBy('client_name');

        return $clients;

    }


public function autoJournals()
{
    $journals = JournalSchedule::leftJoin('scheduled_journals', 'scheduled_journals.journal_schedule_id', '=', 'journal_schedules.journal_schedule_id')
        ->select('journal_schedules.journal_schedule_id', 'amount_due', 'monthly_due')
        ->selectRaw('SUM(CAST(scheduled_journals.amount_settled AS DOUBLE)) AS total_settled')
        ->where('status', 1)
        ->groupBy('journal_schedules.journal_schedule_id', 'amount_due', 'monthly_due')
        ->get();

    foreach ($journals as $journal) {
        if ($journal->amount_due == $journal->total_settled) {
            JournalSchedule::where('journal_schedule_id', $journal->journal_schedule_id)->update(['status' => 3]);
        }else{
            $journalPay = [
                'scheduled_journal_id' => (new  CustomIds())->generateId(),
                'journal_schedule_id' => $journal->journal_schedule_id,
                'amount_settled' => $journal->monthly_due,
                'date_settled' => time()
            ];
            ScheduledJournal::create($journalPay);
        }
    }
}

}
