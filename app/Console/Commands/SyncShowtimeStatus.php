<?php

namespace App\Console\Commands;

use App\Models\Showtime;
use Illuminate\Console\Command;

class SyncShowtimeStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'showtimes:sync-statuses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tự động đồng bộ trạng thái các suất chiếu (ONGOING / COMPLETED) dựa trên thời gian thực';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Đang đồng bộ trạng thái các suất chiếu...');

        Showtime::syncAllStatuses();

        $this->info('Đã đồng bộ trạng thái suất chiếu thành công theo thời gian thực.');

        return Command::SUCCESS;
    }
}
