<?php

namespace App\Console\Commands;

use App\Services\AppClass;
use Illuminate\Console\Command;

class AutoJournals extends Command
{
    protected $appClass;
    public function __construct(AppClass $appClass)
    {
        parent::__construct();
        $this->AppClass = $appClass;
    }
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:auto-journals';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->AppClass->autoJournals();

    }
}
