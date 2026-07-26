<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FakeSchema {
    public static $allBlueprints = [];
    public function create($table, $callback) {
        $connection = \Illuminate\Support\Facades\DB::connection();
        $blueprint = new Blueprint($connection, $table);
        $callback($blueprint);
        self::$allBlueprints[$table] = $blueprint;
    }
    public function table($table, $callback) {}
    public function dropIfExists($table) {}
    public function getConnection() { return \Illuminate\Support\Facades\DB::connection(); }
    public function hasTable($table) { return false; }
    public function __call($method, $args) {}
}

$fakeSchema = new FakeSchema();
Schema::swap($fakeSchema);

$backupDir = __DIR__.'/database/migrations_backup';
$files = glob($backupDir . '/*.php');

foreach ($files as $file) {
    try {
        $migration = require $file;
        if ($migration instanceof Migration) {
            $migration->up();
        }
    } catch (\Throwable $e) {
        echo "Error in file $file: " . $e->getMessage() . "\n";
    }
}

$output = "<?php\n\nuse Illuminate\Database\Migrations\Migration;\nuse Illuminate\Database\Schema\Blueprint;\nuse Illuminate\Support\Facades\Schema;\n\nreturn new class extends Migration\n{\n    public function up(): void\n    {\n";

foreach (FakeSchema::$allBlueprints as $tableName => $blueprint) {
    $commands = $blueprint->getCommands();
    $columns = $blueprint->getColumns();
    
    $tableIndexes = [];
    
    // Extract column-level primary/unique/index
    foreach ($columns as $column) {
        if (!empty($column['primary'])) {
            $tableIndexes[] = "\$table->primary(['{$column['name']}']);";
        }
        if (!empty($column['unique'])) {
            $tableIndexes[] = "\$table->unique('{$column['name']}');";
        }
        if (!empty($column['index'])) {
            $tableIndexes[] = "\$table->index('{$column['name']}');";
        }
    }

    // Extract table-level commands
    foreach ($commands as $command) {
        $name = $command->name;
        if (in_array($name, ['primary', 'unique', 'index', 'spatialIndex', 'foreign'])) {
            if ($name === 'foreign') {
                $cols = json_encode($command->columns);
                $refs = json_encode($command->references);
                $on = $command->on;
                // onUpdate and onDelete
                $onUpdate = !empty($command->onUpdate) ? "->onUpdate('{$command->onUpdate}')" : "";
                $onDelete = !empty($command->onDelete) ? "->onDelete('{$command->onDelete}')" : "";
                
                $tableIndexes[] = "\$table->foreign({$cols})->references({$refs})->on('{$on}'){$onUpdate}{$onDelete};";
            } else {
                $cols = json_encode($command->columns);
                $idxName = $command->index ? "'{$command->index}'" : 'null';
                $tableIndexes[] = "\$table->{$name}({$cols}, {$idxName});";
            }
        }
    }
    
    if (count($tableIndexes) > 0) {
        $output .= "        if (Schema::hasTable('{$tableName}')) {\n";
        $output .= "            Schema::table('{$tableName}', function (Blueprint \$table) {\n";
        foreach ($tableIndexes as $idx) {
            // Need to drop existing index first? No, we assume it's missing.
            // But if it exists, it might fail. It's safer to just let it fail if it exists, or check.
            // PostgreSQL doesn't have an easy Blueprint way to check if an index exists.
            // So we'll just add it.
            $output .= "                " . $idx . "\n";
        }
        $output .= "            });\n";
        $output .= "        }\n\n";
    }
}

$output .= "    }\n\n    public function down(): void\n    {\n        // Not implemented\n    }\n};\n";

file_put_contents(__DIR__.'/database/migrations/2026_07_26_999999_add_missing_indexes_and_foreign_keys.php', $output);
echo "Successfully created 2026_07_26_999999_add_missing_indexes_and_foreign_keys.php\n";
