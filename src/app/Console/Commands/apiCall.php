<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\getTickets;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class apiCall extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:api-call';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'API call to get ticket data';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        try {
            $getTickets = ((new getTickets())->withQuestions());
            Cache::put('getTickets', $getTickets, now()->addMinutes(15));
            Log::info('API call successful');
        } catch (\Throwable $th) {
            Log::error('API call failed with error: ' . $th->getMessage());
        }
    }
}
