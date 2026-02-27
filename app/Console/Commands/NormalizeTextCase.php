<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NormalizeTextCase extends Command
{
    protected $signature = 'data:normalize-titlecase {table} {column}';
    protected $description = 'Normalize existing data to Title Case for a given table+column';

    public function handle()
    {
        $table = $this->argument('table');
        $column = $this->argument('column');

        $rows = DB::table($table)->select('id', $column)->whereNotNull($column)->get();

        $updated = 0;

        foreach ($rows as $r) {
            $old = (string) $r->$column;
            $new = Str::title(trim(preg_replace('/\s+/', ' ', $old)));

            if ($new !== $old) {
                DB::table($table)->where('id', $r->id)->update([$column => $new]);
                $updated++;
            }
        }

        $this->info("Done. Updated {$updated} rows in {$table}.{$column}");
        return Command::SUCCESS;
    }
}