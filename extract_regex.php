<?php

$output = "<?php\n\nuse Illuminate\Database\Migrations\Migration;\nuse Illuminate\Database\Schema\Blueprint;\nuse Illuminate\Support\Facades\Schema;\n\nreturn new class extends Migration\n{\n    public \$withinTransaction = false;\n\n    public function up(): void\n    {\n";
$output .= "        // Set connection to not use transaction so we can catch individual errors\n";
$output .= "        \\Illuminate\\Support\\Facades\\DB::connection()->getPdo()->setAttribute(\\PDO::ATTR_ERRMODE, \\PDO::ERRMODE_EXCEPTION);\n\n";

$deletedFiles = shell_exec('git ls-files --deleted database/migrations/');
$files = array_filter(explode("\n", $deletedFiles));

foreach ($files as $file) {
    $file = trim($file);
    if (empty($file)) continue;
    
    // Get content from git index
    $content = shell_exec('git show HEAD:"' . $file . '"');
    
    if (preg_match('/Schema::create\(\'([^\']+)\', function \(Blueprint \$table\) \{(.*?)\}\);/s', $content, $matches)) {
        $tableName = $matches[1];
        $body = $matches[2];
        
        $tableIndexes = [];
        
        if (preg_match_all('/\$table->(?:[a-zA-Z]+)\(\'([^\']+)\'.*?\)->(?:.*?->)*primary\((.*?)\);/', $body, $m)) {
            foreach ($m[1] as $col) {
                $tableIndexes[] = "\$table->primary(['$col']);";
            }
        }
        if (preg_match_all('/\$table->(?:[a-zA-Z]+)\(\'([^\']+)\'.*?\)->(?:.*?->)*unique\((.*?)\);/', $body, $m)) {
            foreach ($m[1] as $col) {
                $tableIndexes[] = "\$table->unique(['$col']);";
            }
        }
        if (preg_match_all('/\$table->(?:[a-zA-Z]+)\(\'([^\']+)\'.*?\)->(?:.*?->)*index\((.*?)\);/', $body, $m)) {
            foreach ($m[1] as $col) {
                $tableIndexes[] = "\$table->index(['$col']);";
            }
        }
        
        $lines = explode("\n", $body);
        foreach ($lines as $line) {
            $line = trim($line);
            if (preg_match('/^\$table->(primary|unique|index|foreign)\(/', $line)) {
                $tableIndexes[] = $line;
            }
        }
        
        if (count($tableIndexes) > 0) {
            $output .= "        if (Schema::hasTable('{$tableName}')) {\n";
            foreach ($tableIndexes as $idx) {
                $output .= "            try {\n";
                $output .= "                Schema::table('{$tableName}', function (Blueprint \$table) {\n";
                $output .= "                    " . $idx . "\n";
                $output .= "                });\n";
                $output .= "            } catch (\\Exception \$e) {\n";
                $output .= "                echo \"Failed to apply index on {$tableName}: \" . \$e->getMessage() . \"\\n\";\n";
                $output .= "            }\n";
            }
            $output .= "        }\n\n";
        }
    }
}

$output .= "    }\n\n    public function down(): void\n    {\n    }\n};\n";

file_put_contents(__DIR__.'/database/migrations/2026_07_26_999999_add_missing_indexes_and_foreign_keys.php', $output);
echo "Done regex extraction from git with try-catch blocks!\n";
