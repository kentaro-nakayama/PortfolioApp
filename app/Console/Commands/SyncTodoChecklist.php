<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

class SyncTodoChecklist extends Command
{
    protected $signature = 'todo:sync';

    protected $description = 'Run the test suite and check off TODO.md items whose linked test passes.';

    private const TODO_PATH = 'TODO.md';

    public function handle(): int
    {
        $junitPath = storage_path('app/todo-pest-junit.xml');

        $this->info('Running test suite...');
        Process::path(base_path())->run(
            ['./vendor/bin/pest', '--log-junit='.$junitPath]
        );

        if (! file_exists($junitPath)) {
            $this->error('Could not find Pest JUnit report; aborting.');

            return self::FAILURE;
        }

        $testResults = $this->parseJunitReport($junitPath);
        unlink($junitPath);

        $todo = file_get_contents(base_path(self::TODO_PATH));

        $checked = 0;
        $unchecked = 0;
        $missing = [];

        $updated = preg_replace_callback(
            '/^(?<indent>\s*)- \[[ x]\] (?<label>.+?) <!-- test: "(?<test>.+?)" -->$/m',
            function (array $matches) use ($testResults, &$checked, &$unchecked, &$missing): string {
                if (! array_key_exists($matches['test'], $testResults)) {
                    $missing[] = $matches['test'];
                    $mark = ' ';
                } else {
                    $mark = $testResults[$matches['test']] ? 'x' : ' ';
                }

                $mark === 'x' ? $checked++ : $unchecked++;

                return "{$matches['indent']}- [{$mark}] {$matches['label']} <!-- test: \"{$matches['test']}\" -->";
            },
            $todo
        );

        file_put_contents(base_path(self::TODO_PATH), $updated);

        $this->info("Synced TODO.md: {$checked} checked, {$unchecked} unchecked.");

        if ($missing !== []) {
            $this->line('Not yet implemented (no matching test found):');
            foreach ($missing as $testName) {
                $this->line("  - {$testName}");
            }
        }

        return self::SUCCESS;
    }

    /**
     * @return array<string, bool> test name => passed
     */
    private function parseJunitReport(string $path): array
    {
        $xml = simplexml_load_file($path);
        $results = [];

        foreach ($xml->xpath('//testcase') as $testcase) {
            $name = (string) $testcase['name'];
            $passed = ! isset($testcase->failure) && ! isset($testcase->error);
            $results[$name] = $passed;
        }

        return $results;
    }
}
