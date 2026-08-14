<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Showtime;

class CleanupPastShowtimes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'showtimes:cleanup-past';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Soft delete showtimes that have already finished (end_time < now)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting cleanup of past showtimes...');

        // Lấy các suất chiếu đã kết thúc (end_time < now)
        $pastShowtimes = Showtime::where('end_time', '<', now())->get();

        $count = 0;
        foreach ($pastShowtimes as $showtime) {
            $showtime->delete(); // Soft delete
            $count++;
        }

        $this->info("Successfully soft deleted {$count} past showtimes.");
        
        return Command::SUCCESS;
    }
}
