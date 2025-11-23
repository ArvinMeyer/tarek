<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class BackupService
{
    public function exportDatabase(): string
    {
        $filename = 'backup_' . date('Y-m-d_His') . '.sql';
        $path = storage_path('app/backups/' . $filename);

        // Create backups directory if it doesn't exist
        if (!File::exists(storage_path('app/backups'))) {
            File::makeDirectory(storage_path('app/backups'), 0755, true);
        }

        try {
            $tables = $this->getAllTables();
            $sql = $this->generateSqlDump($tables);
            
            File::put($path, $sql);
            
            return $path;
        } catch (\Exception $e) {
            \Log::error('Database backup failed: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function getAllTables(): array
    {
        $tables = [];
        $result = DB::select('SHOW TABLES');
        
        foreach ($result as $row) {
            $row = (array) $row;
            $tables[] = reset($row);
        }
        
        return $tables;
    }

    protected function generateSqlDump(array $tables): string
    {
        $sql = "-- Database Backup\n";
        $sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {
            $sql .= $this->dumpTable($table);
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        return $sql;
    }

    protected function dumpTable(string $table): string
    {
        $sql = "-- Table: {$table}\n";
        $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";

        // Get CREATE TABLE statement
        $createTable = DB::select("SHOW CREATE TABLE `{$table}`")[0];
        $sql .= $createTable->{'Create Table'} . ";\n\n";

        // Get table data
        $rows = DB::table($table)->get();

        if ($rows->count() > 0) {
            foreach ($rows as $row) {
                $row = (array) $row;
                $values = array_map(function($value) {
                    if ($value === null) {
                        return 'NULL';
                    }
                    return "'" . addslashes($value) . "'";
                }, $row);

                $columns = array_keys($row);
                $sql .= "INSERT INTO `{$table}` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $values) . ");\n";
            }
            $sql .= "\n";
        }

        return $sql;
    }

    public function restoreDatabase(string $sqlFilePath): bool
    {
        try {
            if (!File::exists($sqlFilePath)) {
                throw new \Exception('Backup file not found.');
            }

            $sql = File::get($sqlFilePath);
            
            // Split SQL into individual statements
            $statements = array_filter(
                array_map('trim', explode(';', $sql)),
                fn($stmt) => !empty($stmt) && !str_starts_with($stmt, '--')
            );

            DB::beginTransaction();

            foreach ($statements as $statement) {
                if (trim($statement)) {
                    DB::statement($statement);
                }
            }

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Database restore failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getBackupsList(): array
    {
        $backupsPath = storage_path('app/backups');
        
        if (!File::exists($backupsPath)) {
            return [];
        }

        $files = File::files($backupsPath);
        $backups = [];

        foreach ($files as $file) {
            if ($file->getExtension() === 'sql') {
                $backups[] = [
                    'name' => $file->getFilename(),
                    'path' => $file->getPathname(),
                    'size' => $file->getSize(),
                    'date' => date('Y-m-d H:i:s', $file->getMTime()),
                ];
            }
        }

        // Sort by date descending
        usort($backups, fn($a, $b) => $b['date'] <=> $a['date']);

        return $backups;
    }

    public function deleteBackup(string $filename): bool
    {
        $path = storage_path('app/backups/' . $filename);
        
        if (File::exists($path)) {
            return File::delete($path);
        }

        return false;
    }

    public function downloadBackup(string $filename)
    {
        $path = storage_path('app/backups/' . $filename);
        
        if (File::exists($path)) {
            return response()->download($path);
        }

        abort(404, 'Backup file not found.');
    }
}

