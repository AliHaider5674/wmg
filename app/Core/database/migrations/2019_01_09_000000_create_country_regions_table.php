<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Class CreateCountryRegionsTable
 *
 * @category WMG
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class CreateCountryRegionsTable extends Migration
{
    /**
     * Schema table name to migrate
     * @var string
     */
    public $tableName = 'country_regions';
    protected $usRegions = [
        'AL'=>'Alabama', 'AK'=>'Alaska', 'AZ'=>'Arizona', 'AR'=>'Arkansas',
        'CA'=>'California', 'CO'=>'Colorado', 'CT'=>'Connecticut',
        'DE'=>'Delaware', 'DC'=>'District of Columbia', 'FL'=>'Florida',
        'GA'=>'Georgia', 'HI'=>'Hawaii', 'ID'=>'Idaho', 'IL'=>'Illinois',
        'IN'=>'Indiana', 'IA'=>'Iowa', 'KS'=>'Kansas', 'KY'=>'Kentucky',
        'LA'=>'Louisiana', 'ME'=>'Maine', 'MD'=>'Maryland', 'MA'=>'Massachusetts',
        'MI'=>'Michigan', 'MN'=>'Minnesota', 'MS'=>'Mississippi', 'MO'=>'Missouri',
        'MT'=>'Montana', 'NE'=>'Nebraska', 'NV'=>'Nevada', 'NH'=>'New Hampshire',
        'NJ'=>'New Jersey', 'NM'=>'New Mexico', 'NY'=>'New York', 'NC'=>'North Carolina',
        'ND'=>'North Dakota', 'OH'=>'Ohio', 'OK'=>'Oklahoma', 'OR'=>'Oregon',
        'PA'=>'Pennsylvania', 'RI'=>'Rhode Island', 'SC'=>'South Carolina',
        'SD'=>'South Dakota', 'TN'=>'Tennessee', 'TX'=>'Texas', 'UT'=>'Utah',
        'VT'=>'Vermont', 'VA'=>'Virginia', 'WA'=>'Washington', 'WV'=>'West Virginia',
        'WI'=>'Wisconsin', 'WY'=>'Wyoming',
    ];
    /**
     * Run the migrations.
     * @table regions
     *
     * @return void
     */
    public function up()
    {

        if (Schema::hasTable($this->tableName)) {
            return;
        }
        DB::transaction(function () {
            Schema::create($this->tableName, function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->string('code', 45)->nullable();
                $table->string('name');
                $table->string('country_code', 45);
                $table->string('country_name');

                $table->unique(["name", "country_code"], 'UNIQUE_COUNTRY_REGION');
                $table->nullableTimestamps();
            });

            //INSERT US REGIONS
            $timestamp = date("Y-m-d H:i:s");
            foreach ($this->usRegions as $code => $name) {
                DB::table($this->tableName)->insert([
                    'code' => $code,
                    'name' => $name,
                    'country_code' => 'US',
                    'country_name' => 'United States',
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp
                ]);
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists($this->tableName);
    }
}
