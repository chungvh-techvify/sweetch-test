<?php

namespace App\Console\Commands;

use App\Models\Age;
use App\Models\Area;
use App\Models\Ethnicity;
use App\Models\Gender;
use App\Models\Year;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use League\Csv\Reader;
use League\Csv\Statement;

class QueryRow extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'query:row';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'counts the rows From Data8277.csv where
Area is ‘Hampstead’, Age is above 45, Female, Year is 2018, Asian';

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
        echo "Querying...\n";
        $areaCache = Cache::rememberForever('area', function () {
            return Area::select('id', 'code', 'description')->get();
        });
        $ethnicityCache = Cache::rememberForever('ethnicity', function () {
            return Ethnicity::select('id', 'code', 'description')->get();
        });
        $genderCache = Cache::rememberForever('gender', function () {
            return Gender::select('id', 'code', 'description')->get();
        });
        $yearCache = Cache::rememberForever('year', function () {
            return Year::select('id', 'code', 'description')->get();
        });

        // query conditions
        $areaCode = $areaCache->firstWhere('description', 'Hampstead')->code;
        $genderCode = $genderCache->firstWhere('description', 'Female')->code;
        $yearCode = $yearCache->firstWhere('description', 2018)->code;
        $ethnicityCode = $ethnicityCache->firstWhere('description', 'Asian')->code;
        $ageCodes = array_map(function ($age) {
            if ($age < 100) {
                return '0'.$age;
            }
            return $age;
        }, range(46, 120));

        $csv = Reader::createFromPath(config('filesystems.csv').'/Data8277.csv');
        $csv->setHeaderOffset(0);

        $stmt = Statement::create(function ($row) use ($areaCode, $genderCode, $yearCode, $ethnicityCode, $ageCodes) {
            return (
                $row['Area'] == $areaCode &&
                in_array($row['Age'], $ageCodes, true) &&
                $row['Sex'] == $genderCode &&
                $row['Year'] == $yearCode &&
                $row['Ethnic'] == $ethnicityCode
            );
        });
        $count = $stmt->process($csv)->count();
        echo "Number of results: ${count}\n";
        return 0;
    }
}
