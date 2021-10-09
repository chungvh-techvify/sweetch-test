<?php

namespace App\Console\Commands;

use App\Helpers\CommonHelper;
use App\Jobs\InsertItems;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use League\Csv\Reader;
use League\Csv\Statement;

class ImportItems extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:items';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import data into `items` table';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $startTime = microtime(true);
        Log::info('Start import');
        $csv = Reader::createFromPath(env('DATA_CSV_PATH'));
        $csv->setHeaderOffset(0);

        $stmt = Statement::create();

        // split data into chunks, each chunk contain LIMIT_INSERT_ROWS rows
        $limit = env('LIMIT_INSERT_ROWS', 1000);
        $page = 1;
        $continue = true;

        do {
            $offset = ($page - 1) * $limit;
            $items = $stmt->offset($offset)->limit($limit)->process($csv);
            InsertItems::dispatch($items);
            if (!count($items)) {
                break;
            }
            echo "Processing page ${page}...\n";
            Log::info("Processing page ${page}...");
            $page += 1;
        } while ($continue);

        $endTime = microtime(true);
        $totalTime = $endTime - $startTime;
        echo "Created all jobs. Total run time: ${totalTime} ms\n";
        return 0;
    }
}
