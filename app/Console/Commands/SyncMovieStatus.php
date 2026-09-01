<?php

namespace App\Console\Commands;

use App\Models\Movie;
use Illuminate\Console\Command;

class SyncMovieStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'movies:sync-statuses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tự động chuyển trạng thái phim (PRE_ORDER / NOW_SHOWING) và kích hoạt suất chiếu draft dựa trên thời gian thực';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Đang đồng bộ trạng thái các bộ phim...');

        $updatedCount = Movie::syncAllStatuses();

        $this->info("Đã đồng bộ trạng thái thành công ($updatedCount bộ phim được cập nhật).");

        return Command::SUCCESS;
    }
}
