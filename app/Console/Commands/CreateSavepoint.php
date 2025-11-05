<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\Storage;

class CreateSavepoint extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'savepoint:create {--message= : Commit message}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a git savepoint and a MySQL dump in database/dumps';

    public function handle()
    {
        $message = $this->option('message') ?: 'savepoint: automatic';

        // Ensure dumps directory exists
        if (!is_dir(database_path('dumps'))) {
            mkdir(database_path('dumps'), 0755, true);
        }

        $timestamp = date('Ymd\THis');
        $dumpPath = database_path("dumps/savepoint-{$timestamp}.sql");
        $metaPath = database_path("dumps/savepoint-{$timestamp}.md");

        // Git add & commit
        $this->line('Staging changes and committing...');
        $process = Process::fromShellCommandline("git add -A && git commit -m \"{$message}\" || true");
        $process->setTimeout(300);
        $process->run();

        // Get commit hash
        $process = Process::fromShellCommandline('git rev-parse HEAD');
        $process->run();
        $commit = trim($process->getOutput() ?: '');

        // Read DB credentials from env
        $dbHost = env('DB_HOST', '127.0.0.1');
        $dbPort = env('DB_PORT', '3306');
        $dbName = env('DB_DATABASE');
        $dbUser = env('DB_USERNAME');
        $dbPass = env('DB_PASSWORD');

        if (!$dbName || !$dbUser) {
            $this->error('Missing DB credentials in .env. Skipping dump.');
            file_put_contents($metaPath, "Commit: {$commit}\nDump: SKIPPED\nTimestamp: " . gmdate('Y-m-d\TH:i:s\Z') . "\n");
            $this->info("Saved metadata to {$metaPath}");
            return 0;
        }

        // Run mysqldump
        $this->line('Running mysqldump...');
        $escapedPass = str_replace("'", "'\\''", $dbPass);
        $cmd = "mysqldump -h{$dbHost} -P{$dbPort} -u {$dbUser} -p'{$escapedPass}' {$dbName} > {$dumpPath}";
        $process = Process::fromShellCommandline($cmd);
        $process->setTimeout(600);
        $process->run();

        if (!$process->isSuccessful()) {
            $this->error('mysqldump failed: ' . $process->getErrorOutput());
            throw new ProcessFailedException($process);
        }

        // Write metadata
        $counts = [];
        try {
            $countCards = trim(shell_exec("mysql -h{$dbHost} -P{$dbPort} -u {$dbUser} -p'{$escapedPass}' -D {$dbName} -se \"SELECT COUNT(*) FROM rfid_cards;\""));
            $countStudents = trim(shell_exec("mysql -h{$dbHost} -P{$dbPort} -u {$dbUser} -p'{$escapedPass}' -D {$dbName} -se \"SELECT COUNT(*) FROM users u JOIN model_has_roles m ON u.id = m.model_id JOIN roles r ON m.role_id = r.id WHERE m.model_type='App\\\\Models\\\\User' AND r.name='student';\""));
        } catch (\Exception $e) {
            $countCards = 'unknown';
            $countStudents = 'unknown';
        }

        $meta = "Commit: {$commit}\nDump: {$dumpPath}\nTimestamp: " . gmdate('Y-m-d\TH:i:s\Z') . "\nmysqldump: OK\nRFID cards count:\n{$countCards}\nStudents with role 'student' count:\n{$countStudents}\n";
        file_put_contents($metaPath, $meta);

        $this->info("Savepoint created. Dump: {$dumpPath}");
        $this->info("Metadata: {$metaPath}");

        return 0;
    }
}
