<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

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
            if (Schema::hasColumn('leads', 'country_id')) {
                try { $table->dropForeign(['country_id']); } catch (\Exception $e) { Log::warning('Could not drop FK leads.country_id', ['error' => $e->getMessage()]); }
            }
            if (Schema::hasColumn('leads', 'state_id')) {
                try { $table->dropForeign(['state_id']); } catch (\Exception $e) { Log::warning('Could not drop FK leads.state_id', ['error' => $e->getMessage()]); }
            }
            if (Schema::hasColumn('leads', 'city_id')) {
                try { $table->dropForeign(['city_id']); } catch (\Exception $e) { Log::warning('Could not drop FK leads.city_id', ['error' => $e->getMessage()]); }
            }
            $cols = [];
            if (Schema::hasColumn('leads', 'country_id')) { $cols[] = 'country_id'; }
            if (Schema::hasColumn('leads', 'state_id')) { $cols[] = 'state_id'; }
            if (Schema::hasColumn('leads', 'city_id')) { $cols[] = 'city_id'; }
            if (! empty($cols)) { $table->dropColumn($cols); }
            if (! Schema::hasColumn('leads', 'country')) {
                $table->string('country')->nullable()->after('address');
            }
            if (! Schema::hasColumn('leads', 'state')) {
                $table->string('state')->nullable()->after('country');
            }
            if (! Schema::hasColumn('leads', 'city')) {
                $table->string('city')->nullable()->after('state');
            }
        });

        // ORGANIZATIONS
        Schema::table('organizations', function (Blueprint $table) {
            if (Schema::hasColumn('organizations', 'country_id')) {
                try { $table->dropForeign(['country_id']); } catch (\Exception $e) { Log::warning('Could not drop FK organizations.country_id', ['error' => $e->getMessage()]); }
            }
            if (Schema::hasColumn('organizations', 'state_id')) {
                try { $table->dropForeign(['state_id']); } catch (\Exception $e) { Log::warning('Could not drop FK organizations.state_id', ['error' => $e->getMessage()]); }
            }
            if (Schema::hasColumn('organizations', 'city_id')) {
                try { $table->dropForeign(['city_id']); } catch (\Exception $e) { Log::warning('Could not drop FK organizations.city_id', ['error' => $e->getMessage()]); }
            }
            $cols = [];
            if (Schema::hasColumn('organizations', 'country_id')) { $cols[] = 'country_id'; }
            if (Schema::hasColumn('organizations', 'state_id')) { $cols[] = 'state_id'; }
            if (Schema::hasColumn('organizations', 'city_id')) { $cols[] = 'city_id'; }
            if (! empty($cols)) { $table->dropColumn($cols); }
            if (! Schema::hasColumn('organizations', 'country')) {
                $table->string('country')->nullable()->after('address2');
            }
            if (! Schema::hasColumn('organizations', 'state')) {
                $table->string('state')->nullable()->after('country');
            }
            if (! Schema::hasColumn('organizations', 'city')) {
                $table->string('city')->nullable()->after('state');
            }
        });

        // USER_DETAILS
        Schema::table('user_details', function (Blueprint $table) {
            if (Schema::hasColumn('user_details', 'country_id')) {
                try { $table->dropForeign(['country_id']); } catch (\Exception $e) { Log::warning('Could not drop FK user_details.country_id', ['error' => $e->getMessage()]); }
            }
            if (Schema::hasColumn('user_details', 'state_id')) {
                try { $table->dropForeign(['state_id']); } catch (\Exception $e) { Log::warning('Could not drop FK user_details.state_id', ['error' => $e->getMessage()]); }
            }
            if (Schema::hasColumn('user_details', 'city_id')) {
                try { $table->dropForeign(['city_id']); } catch (\Exception $e) { Log::warning('Could not drop FK user_details.city_id', ['error' => $e->getMessage()]); }
            }
            $cols = [];
            if (Schema::hasColumn('user_details', 'country_id')) { $cols[] = 'country_id'; }
            if (Schema::hasColumn('user_details', 'state_id')) { $cols[] = 'state_id'; }
            if (Schema::hasColumn('user_details', 'city_id')) { $cols[] = 'city_id'; }
            if (! empty($cols)) { $table->dropColumn($cols); }
            if (! Schema::hasColumn('user_details', 'country')) {
                $table->string('country')->nullable()->after('address');
            }
            if (! Schema::hasColumn('user_details', 'state')) {
                $table->string('state')->nullable()->after('country');
            }
            if (! Schema::hasColumn('user_details', 'city')) {
                $table->string('city')->nullable()->after('state');
            }
        });

        // USERS
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'country_id')) {
                try { $table->dropForeign(['country_id']); } catch (\Exception $e) { Log::warning('Could not drop FK users.country_id', ['error' => $e->getMessage()]); }
                $table->dropColumn('country_id');
            }
            if (! Schema::hasColumn('users', 'country')) {
                $table->string('country')->nullable()->after('phone');
            }
        });
    }
};
