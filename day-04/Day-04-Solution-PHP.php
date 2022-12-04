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
 *  $ cat example-input.txt | php -f Day-04-Solution-PHP.php
 *
 *  # Read from default `input.txt`
 *  $ php -f Day-04-Solution-PHP.php
 *
 *  # Run tests on example data
 *  $ php -f Day-04-Solution-PHP.php test
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

$i = 0;
$pairs = array();
$overlapping_count_partial = 0;
$overlapping_count_complete = 0;

foreach ($input as $k => $v) {
    /*
        Process each CSV row, each pair (column) identified as a range.
        Fill out that range of numbers for each pair.
        Find pairs that have overlapping ranges.
    */
    $pairs[$i] = array_map(function ($q) {
        return range($q[0], $q[1]);
    }, array_map(function ($p) {
        return array_map(function ($r) {
            return $r;
        }, explode("-", $p));
    }, explode(",", $v)));

    $intersect = array_intersect($pairs[$i][0], $pairs[$i][1]);

    if (count($intersect) > 0) {
        $overlapping_count_partial++;
    }

    /*
        A complete overlap is one where the number of intersections
        matches the count of an entire range in the pair.
    */
    if (
        in_array(count($intersect), array(
            count($pairs[$i][0]),
            count($pairs[$i][1])
        ))
    ) {
        $overlapping_count_complete++;
    }

    $i++;
}

// Declare our answers
$answers = array(
    (int) $overlapping_count_complete,
    (int) $overlapping_count_partial
);

print "Part One: In how many assignment pairs does one range fully contain the other? = ${answers[0]}\n";
print "Part Two: In how many assignment pairs do the ranges overlap? = ${answers[1]}\n";

if ($testMode) {
    $testAnswers = array_map('intval', (new ReadInputFile)->FromFile('example-input-answers.txt')->getInputArray());

    print "\n\nTESTS\n=====\n";

    for ($i = 0; $i < count($answers); $i++) {
        print "\tPart " . ($i + 1) . ": " . ($answers[$i] === $testAnswers[$i] ? "PASSED" : "failed! Expected Value: ${testAnswers[$i]}") . "\n";
    }
}
