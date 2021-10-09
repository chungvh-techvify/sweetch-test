<?php

namespace App\Jobs;

use App\Helpers\CommonHelper;
use App\Models\Age;
use App\Models\Area;
use App\Models\Ethnicity;
use App\Models\Gender;
use App\Models\Year;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InsertItems implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected $items;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($items)
    {
        // convert League\Csv\ResultSet to array
        $this->items = iterator_to_array($items, true);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $ageCache = Cache::rememberForever('age', function () {
                return Age::select('id', 'code')->get();
            });
            $areaCache = Cache::rememberForever('area', function () {
                return Area::select('id', 'code')->get();
            });
            $ethnicityCache = Cache::rememberForever('ethnicity', function () {
                return Ethnicity::select('id', 'code')->get();
            });
            $genderCache = Cache::rememberForever('gender', function () {
                return Gender::select('id', 'code')->get();
            });
            $yearCache = Cache::rememberForever('year', function () {
                return Year::select('id', 'code')->get();
            });
            // find id where code='000'
            $insertData = array_map(function ($item) use (
                $ageCache,
                $areaCache,
                $ethnicityCache,
                $genderCache,
                $yearCache
            ) {
                $ageId = $ageCache->firstWhere('code', $item['Age'])->id;
                $areaId = $areaCache->firstWhere('code', $item['Area'])->id;
                $ethnicityId = $ethnicityCache->firstWhere('code', $item['Ethnic'])->id;
                $genderId = $genderCache->firstWhere('code', $item['Sex'])->id;
                $yearId = $yearCache->firstWhere('code', $item['Year'])->id;
                return [
                    'age_id' => $ageId,
                    'area_id' => $areaId,
                    'ethnicity_id' => $ethnicityId,
                    'gender_id' => $genderId,
                    'year_id' => $yearId,
                    'count' => ($item['count'] === '..C') ? 0 : $item['count']
                ];
            }, $this->items);

            DB::table('items')->insert($insertData);
            CommonHelper::logMemoryUsage();
        } catch (\Exception $exception) {
            Log::error('Insert item error: '.$exception->getMessage());
        }
    }
}
