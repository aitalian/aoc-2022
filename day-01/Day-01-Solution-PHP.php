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
 *  $ cat example-input.txt | php -f Day-01-Solution-PHP.php
 *
 *  # Read from default `input.txt`
 *  $ php -f Day-01-Solution-PHP.php
 *
 *  # Run tests on example data
 *  $ php -f Day-01-Solution-PHP.php test
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
            $this->input = array_map('trim', explode("\n", file_get_contents($filename)));
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

$count_elves = 0;
$elves_calories = array();

foreach ($input as $k => $v) {
    if (empty($v)) {
        $count_elves++;
        continue;
    }
    if (!array_key_exists($count_elves, $elves_calories)) {
        $elves_calories[$count_elves] = 0;
    }

    $elves_calories[$count_elves] += (int) $v;
}

rsort($elves_calories, SORT_NUMERIC);

// Declare our answers
$answers = array(
    (int) max($elves_calories),
    array_sum(array($elves_calories[0], $elves_calories[1], $elves_calories[2]))
);

print "Part One: Total Calories of the Elf carrying the most Calories = ${answers[0]}\n";
print "Part Two: Total Calories of the top three Elves carrying the most Calories = ${answers[1]}\n";

if ($testMode) {
    $testAnswers = array_map('intval', (new ReadInputFile)->FromFile('example-input-answers.txt')->getInputArray());

    print "\n\nTESTS\n=====\n";

    for ($i = 0; $i < count($answers); $i++) {
        print "\tPart " . ($i + 1) . ": " . ($answers[$i] === $testAnswers[$i] ? "PASSED" : "failed! Expected Value: ${testAnswers[$i]}") . "\n";
    }
}
