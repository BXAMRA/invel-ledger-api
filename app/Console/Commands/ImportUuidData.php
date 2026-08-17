<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ImportUuidData extends Command
{
    protected $signature = 'data:import-uuids {--file=database_uuid_backup.json}';
    protected $description = 'Import mapped UUID JSON data back into the database.';

    public function handle()
    {
        $file = storage_path('app/' . $this->option('file'));
        
        if (!File::exists($file)) {
            $this->error("Backup file not found at {$file}");
            return;
        }

        $this->info('Reading JSON backup...');
        $data = json_decode(File::get($file), true);

        if (!$data) {
            $this->error('Failed to parse JSON file.');
            return;
        }

        $this->info('Importing data...');

        // Disable foreign key checks to allow arbitrary insertion order
        DB::statement('PRAGMA foreign_keys = OFF;'); // For SQLite

        DB::transaction(function () use ($data) {
            foreach ($data as $table => $rows) {
                if (empty($rows)) continue;

                $this->info("Inserting into {$table} (" . count($rows) . " rows)...");
                
                // Chunk inserts to avoid memory/parameter limits
                $chunks = array_chunk($rows, 100);
                foreach ($chunks as $chunk) {
                    DB::table($table)->insert($chunk);
                }
            }
        });

        DB::statement('PRAGMA foreign_keys = ON;'); // Re-enable for SQLite

        $this->info('Successfully imported all mapped data!');
    }
}
