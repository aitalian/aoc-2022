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
 *  $ cat example-input.txt | php -f Day-06-Solution-PHP.php
 *
 *  # Read from default `input.txt`
 *  $ php -f Day-06-Solution-PHP.php
 *
 *  # Run tests on example data
 *  $ php -f Day-06-Solution-PHP.php test
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

/**
 * Given a $set and number of $bits to check as well as a $startbit
 * Check if the set is unique.
 * - Return an array where:
 *   - key 0 contains an array of the unique bits
 *   - key 1 contains the position in the original set
 * - Return false if no matching bits found
 * 
 */
function unique_bits($set, $bits = 4, $startbit = 4) {
    for ($i=$startbit; $i < count($set); $i++) {
        $arr = array();

        for ($b=$bits; $b > 0; $b--) {
            $arr[] = ($set[$i - $b] ?? null);
        }

        $check = array_filter(
            $arr, function ($v, $k) {
                return !is_null($v);
            }, ARRAY_FILTER_USE_BOTH
        );

        if (count(array_unique($check)) == $bits) {
            return array($check, $i);
        }
    }

    return false;
}

// Start-of-packet marker is 4 bits long, starting at 4th bit
$part1_answer = unique_bits(str_split($input[0], 1), 4, 4)[1];

// Start-of-message marker is 14 bits long, starting at 4th bit
$part2_answer = unique_bits(str_split($input[0], 1), 14, 4)[1];

// Declare our answers
$answers = array(
    (int) $part1_answer,
    (int) $part2_answer,
);

print "Part One: How many characters need to be processed before the first start-of-packet marker is detected? = ${answers[0]}\n";
print "Part Two: How many characters need to be processed before the first start-of-message marker is detected? = ${answers[1]}\n";

if ($testMode) {
    $testAnswers = array_map('intval', (new ReadInputFile)->FromFile('example-input-answers.txt')->getInputArray());

    print "\n\nTESTS\n=====\n";

    for ($i = 0; $i < count($answers); $i++) {
        print "\tPart " . ($i + 1) . ": " . ($answers[$i] === $testAnswers[$i] ? "PASSED" : "failed! Expected Value: ${testAnswers[$i]}") . "\n";
    }
}
