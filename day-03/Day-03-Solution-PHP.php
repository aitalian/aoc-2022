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
 *  $ cat example-input.txt | php -f Day-03-Solution-PHP.php
 *
 *  # Read from default `input.txt`
 *  $ php -f Day-03-Solution-PHP.php
 *
 *  # Run tests on example data
 *  $ php -f Day-03-Solution-PHP.php test
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

/*
    - each character is an item type and is case-sensitive (a != A)
    - rucksack = 1 row
    - each has 2 equal sized parts (compartment = half the # of items)
    - get unique items in each compartment and find the one that appears in both sets
    - convert item types to a priority:
        - [a-z] = 1-26 respective
        - [A-Z] = 27-52 respective
    - for the duplicate items, get the priority #
    - then sum them all
*/

$i = 0;
$dupes = array();
$dupe_priorities = array();

// Fill an array of priority values
// [a-z] = 1-26
// [A-Z] = 27-52
$priorities = array();

foreach (range('a', 'z') as $k => $v) {
    $priorities[$k + 1] = $v;
}

foreach (range('A', 'Z') as $k => $v) {
    $priorities[$k + 27] = $v;
}

$priorities = array_flip($priorities);

foreach ($input as $k => $v) {
    // split the sack into two compartments
    $sack = str_split($v, (strlen($v) / 2));
    $compartments = array(array_unique(str_split($sack[0],1)), array_unique(str_split($sack[1], 1)));
    $dupes[$i] = implode('', array_intersect($compartments[0], $compartments[1]));
    // get the matching priority # for the dupe
    $dupe_priorities[$i] = $priorities[$dupes[$i]];

    $i++;
}

// Part 2
// process 3 lines for each group
$sack_badges = array();
$sack_badges_priorities = array();

for ($g = 1; $g <= count($input); $g++) {
    if ($g % 3 == 0) {
        $sack_badges[$g] = implode('', array_intersect(
            array_unique(str_split($input[$g-3], 1)),
            array_unique(str_split($input[$g-2], 1)),
            array_unique(str_split($input[$g-1], 1)),
        ));

        $sack_badges_priorities[$g] = $priorities[$sack_badges[$g]];
    }
}

// Declare our answers
$answers = array(
    (int) array_sum($dupe_priorities),
    (int) array_sum($sack_badges_priorities)
);

print "Part One: Find the item type that appears in both compartments of each rucksack. What is the sum of the priorities of those item types? = ${answers[0]}\n";
print "Part Two: Find the item type that corresponds to the badges of each three-Elf group. What is the sum of the priorities of those item types? = ${answers[1]}\n";

if ($testMode) {
    $testAnswers = array_map('intval', (new ReadInputFile)->FromFile('example-input-answers.txt')->getInputArray());

    print "\n\nTESTS\n=====\n";

    for ($i = 0; $i < count($answers); $i++) {
        print "\tPart " . ($i + 1) . ": " . ($answers[$i] === $testAnswers[$i] ? "PASSED" : "failed! Expected Value: ${testAnswers[$i]}") . "\n";
    }
}
