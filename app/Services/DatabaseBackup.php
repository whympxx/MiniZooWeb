<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Backup database dengan PHP murni (tidak butuh mysqldump di PATH).
 * Hasilnya file .sql berisi struktur (khusus MySQL/MariaDB) dan data.
 */
class DatabaseBackup
{
    public const BACKUP_DIR = 'backups';

    public const TABLES = ['users', 'orders', 'settings', 'login_logs'];

    public function create(): string
    {
        $connection = DB::connection();
        $isMysql = in_array($connection->getDriverName(), ['mysql', 'mariadb'], true);
        $pdo = $connection->getPdo();

        $sql = "-- Zoo Management System backup\n-- Dibuat: ".now()->toDateTimeString()."\n\n";
        if ($isMysql) {
            $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";
        }

        foreach (self::TABLES as $table) {
            if ($isMysql) {
                $create = (array) DB::selectOne("SHOW CREATE TABLE `$table`");
                $sql .= "DROP TABLE IF EXISTS `$table`;\n".end($create).";\n\n";
            }

            foreach (DB::table($table)->get() as $row) {
                $row = (array) $row;
                $columns = implode(', ', array_map(fn ($c) => "`$c`", array_keys($row)));
                $values = implode(', ', array_map(
                    fn ($v) => $v === null ? 'NULL' : $pdo->quote((string) $v),
                    array_values($row)
                ));
                $sql .= "INSERT INTO `$table` ($columns) VALUES ($values);\n";
            }
            $sql .= "\n";
        }

        if ($isMysql) {
            $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
        }

        $filename = 'backup_'.now()->format('Y-m-d_H-i-s').'.sql';
        Storage::disk('local')->put(self::BACKUP_DIR.'/'.$filename, $sql);

        return $filename;
    }

    public function lastBackupTime(): ?string
    {
        $files = Storage::disk('local')->files(self::BACKUP_DIR);
        if (! $files) {
            return null;
        }

        $latest = collect($files)->max(fn ($f) => Storage::disk('local')->lastModified($f));

        return date('Y-m-d H:i:s', $latest);
    }
}
