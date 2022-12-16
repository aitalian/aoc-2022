<?php

/**
 * Input data should be provided in `input.txt`.
 * Example data rows for testing should be in `example-input.txt`.
 * Tests will look for matching answers in `example-input-answers.txt` (one per line).
 * Input data can also be provided from stdin.
 *
 * Example CLI usage:
 * ```sh
 *  # Read from STDIN
 *  $ cat example-input.txt | php -f Day-07-Solution-PHP.php
 *
 *  # Read from default `input.txt`
 *  $ php -f Day-07-Solution-PHP.php
 *
 *  # Run tests on example data
 *  $ php -f Day-07-Solution-PHP.php test
 * ```
 */

class ReadInputFile
{
    public $input = array();

    public function FromSTDIN($fallbackFromFile = true)
    {
        $stdin = fopen('php://stdin', 'r');

        if (is_resource($stdin)) {
            stream_set_blocking($stdin, 0);

            while (($f = fgets($stdin)) !== false) {
                $this->input[] = trim($f);
            }

            fclose($stdin);
        }

        if (empty($this->input)) {
            if ($fallbackFromFile) {
                $this->FromFile('input.txt');
            }
        }

        return $this;
    }

    public function FromFile($filename = 'input.txt')
    {
        if (file_exists($filename)) {
            $contents = file_get_contents($filename);
            $this->input = array_map('trim', explode("\n", $contents));

            if (empty($this->input)) {
                $this->input[] = $contents;
            }
        }

        return $this;
    }

    public function getInputArray()
    {
        return $this->input;
    }

}

$testMode = false;

if (isset($argv) && !empty($argv[1])) {
    if ($argv[1] == "test") {
        $testMode = true;
    }
}

if ($testMode) {
    $input = (new ReadInputFile)->FromFile('example-input.txt')->getInputArray();
} else {
    $input = (new ReadInputFile)->FromSTDIN(true)->getInputArray();
}

# ----- BEGIN Puzzle

$cd = "./";
$items = [];
$directory_sizes = [];

foreach ($input as $line) {
    $cmd = explode(" ", $line);

    switch ($cmd[0]) {
        case "$":
            // cmd prompt
            switch ($cmd[1]) {
                case "cd":
                    switch ($cmd[2]) {
                        case "/":
                            // root - not leaving this as just "/" since we use that to split the path later.
                            $cd = "./";
                            break;

                        case "..":
                            // up a level
                            $cd = (strrpos($cd, "/", -1)) ? substr($cd, 0, strrpos($cd, "/", -1) - 1) : "./";
                            break;

                        default:
                            // down a level
                            $cd .= $cmd[2] . "/";
                            break;
                    }
                    break;

                case "ls":
                default:
                    // do nothing for now
                    break;
            }
            break;

        case "dir":
            // dir found in output: $cmd[1] = dirname
            // do nothing for now
            break;

        default:
            // all other output: $cmd[0] = filesize; $cmd[1] = filename
            // add filesize with path for this item
            if (is_numeric($cmd[0])) {
                $items[$cd][$cmd[1]] = (int) $cmd[0];
            }
            break;
    }
}

// Tally up filesize of all directories
foreach ($items as $dir => $files) {
    $cd = "";

    // break down subdirs
    foreach (explode("/", $dir) as $subdir) {
        if (empty($subdir)) {
            continue;
        }

        $cd = implode("/", [$cd, $subdir]);

        if (!array_key_exists($cd, $directory_sizes)) {
            // initialize a count
            $directory_sizes[$cd] = 0;
        }

        // add 'em up!
        $directory_sizes[$cd] += array_sum($files);
    }
}

$part1_answer = 0;

$total_disk_space = 70000000;
$free_space_required = 30000000;

$current_free_space = $total_disk_space - $directory_sizes["/."];
$min_space_to_free = $free_space_required - $current_free_space;

$part2_answer = $directory_sizes["/."];

foreach ($directory_sizes as $size) {
    if ($size <= 100000) {
        // Part 1 - total size of directories <= 100000
        $part1_answer += $size;
    }

    if ($size < $part2_answer) {
        if ($size >= $min_space_to_free) {
            // Part 2 - Find smallest directory to delete to free up enough space
            $part2_answer = $size;
        }
    }
}

// Declare our answers
$answers = array(
    (int) $part1_answer,
    (int) $part2_answer,
);

print "Part One: Find all of the directories with a total size of at most 100000. What is the sum of the total sizes of those directories? = ${answers[0]}\n";
print "Part Two: Find the smallest directory that, if deleted, would free up enough space on the filesystem to run the update. What is the total size of that directory? = ${answers[1]}\n";

if ($testMode) {
    $testAnswers = array_map('intval', (new ReadInputFile)->FromFile('example-input-answers.txt')->getInputArray());

    print "\n\nTESTS\n=====\n";

    for ($i = 0; $i < count($answers); $i++) {
        print "\tPart " . ($i + 1) . ": " . ($answers[$i] === $testAnswers[$i] ? "PASSED" : "failed! Expected Value: ${testAnswers[$i]}") . "\n";
    }
}
