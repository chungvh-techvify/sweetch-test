<?php

namespace App\Console\Commands;

use App\Helpers\CommonHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;
use League\Csv\Statement;

class ImportBase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:base';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import references tables';

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
        $tableMapping = [
            'ages' => env('AGE_CSV_PATH'),
            'areas' => env('AREA_CSV_PATH'),
            'ethnicities' => env('ETHNIC_CSV_PATH'),
            'gender' => env('GENDER_CSV_PATH'),
            'years' => env('YEAR_CSV_PATH')
        ];

        foreach ($tableMapping as $table => $file) {
            $csv = Reader::createFromPath($file);
            $csv->setHeaderOffset(0);

            $records = Statement::create()->process($csv);
            $items = [];
            foreach ($records as $record) {
                $items[] = [
                    'code' => $record['Code'],
                    'description' => $record['Description'],
                    'sort_order' => $record['SortOrder'],
                ];
            }

            DB::table($table)->insert($items);
            $countItem = count($items);
            echo "\nImport ${countItem} items into `${table}` table\n";
            CommonHelper::printMemoryUsage();
        }

        CommonHelper::printMemoryUsage();

        $endTime = microtime(true);
        $runTime = $endTime - $startTime;
        echo "Total run time: ${runTime} ms\n";
        return 0;
    }
}
