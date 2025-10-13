<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateWithoutPrimaryKeyCheck extends Command
{
    protected $signature = 'migrate:no-pk-check {--force}';
    protected $description = 'Run migrations with sql_require_primary_key disabled';

    public function handle()
    {
        try {
            DB::statement('SET SESSION sql_require_primary_key=0');
            $this->call('migrate', ['--force' => $this->option('force')]);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return 1;
        }
        
        return 0;
    }
}