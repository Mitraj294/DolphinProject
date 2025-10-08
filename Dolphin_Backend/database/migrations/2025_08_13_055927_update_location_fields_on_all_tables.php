<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        // Define FK action constant
        if (! defined('LOCATION_FK_ACTION')) {
            define('LOCATION_FK_ACTION', 'set null');
        }

        // helper to drop legacy columns
        $dropCols = function (string $tableName, array $drops): void {
            Schema::table($tableName, function (Blueprint $table) use ($tableName, $drops) {
                foreach ($drops as $dropCol) {
                    if (Schema::hasColumn($tableName, $dropCol)) {
                        $table->dropColumn($dropCol);
                    }
                }
            });
        };

        // helper to add column + optional FK
        $addLocationColumn = function (string $tableName, string $columnName, ?string $afterColumn, string $refTable) {
            if (Schema::hasColumn($tableName, $columnName)) {
                return;
            }
            Schema::table($tableName, function (Blueprint $table) use ($columnName, $afterColumn) {
                if ($afterColumn) {
                    $table->unsignedBigInteger($columnName)->nullable()->after($afterColumn);
                } else {
                    $table->unsignedBigInteger($columnName)->nullable();
                }
            });
            if (Schema::hasTable($refTable)) {
                Schema::table($tableName, function (Blueprint $table) use ($columnName, $refTable) {
                    $table->foreign($columnName)->references('id')->on($refTable)->onDelete(LOCATION_FK_ACTION);
                });
            }
        };

        // table-driven configuration
        $tableConfigs = [
            'leads' => [
                'drops' => ['country', 'state', 'city'],
                'cols' => [
                    ['country_id','address','countries'],
                    ['state_id','country_id','states'],
                    ['city_id','state_id','cities'],
                ],
            ],
            'organizations' => [
                'drops' => ['country', 'state', 'city'],
                'cols' => [
                    ['country_id','address2','countries'],
                    ['state_id','country_id','states'],
                    ['city_id','state_id','cities'],
                ],
            ],
            'user_details' => [
                'drops' => ['country', 'state', 'city'],
                'cols' => [
                    ['country_id','address','countries'],
                    ['state_id','country_id','states'],
                    ['city_id','state_id','cities'],
                ],
            ],
            'users' => [
                'drops' => ['country'],
                'cols' => [
                    ['country_id','phone','countries'],
                ],
            ],
        ];

        foreach ($tableConfigs as $tableName => $cfg) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }
            $dropCols($tableName, $cfg['drops']);
            foreach ($cfg['cols'] as $col) {
                $addLocationColumn($tableName, $col[0], $col[1], $col[2]);
            }
        }
}

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        // LEADS
        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
            $table->dropForeign(['state_id']);
            $table->dropForeign(['city_id']);
            $table->dropColumn(['country_id', 'state_id', 'city_id']);
            $table->string('country')->nullable()->after('address');
            $table->string('state')->nullable()->after('country');
            $table->string('city')->nullable()->after('state');
        });

        // ORGANIZATIONS
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
            $table->dropForeign(['state_id']);
            $table->dropForeign(['city_id']);
            $table->dropColumn(['country_id', 'state_id', 'city_id']);
            $table->string('country')->nullable()->after('address2');
            $table->string('state')->nullable()->after('country');
            $table->string('city')->nullable()->after('state');
        });

        // USER_DETAILS
        Schema::table('user_details', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
            $table->dropForeign(['state_id']);
            $table->dropForeign(['city_id']);
            $table->dropColumn(['country_id', 'state_id', 'city_id']);
            $table->string('country')->nullable()->after('address');
            $table->string('state')->nullable()->after('country');
            $table->string('city')->nullable()->after('state');
        });

        // USERS
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
            $table->dropColumn('country_id');
            $table->string('country')->nullable()->after('phone');
        });
    }
};
