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
 *  $ cat example-input.txt | php -f Day-02-Solution-PHP.php
 *
 *  # Read from default `input.txt`
 *  $ php -f Day-02-Solution-PHP.php
 *
 *  # Run tests on example data
 *  $ php -f Day-02-Solution-PHP.php test
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

$lookup_table_score = array(
    "X" => 1,   // Rock
    "Y" => 2,   // Paper
    "Z" => 3    // Scissors
);

$lookup_table_shape = array(
    "A" => "X", // Rock
    "B" => "Y", // Paper
    "C" => "Z"  // Scissors
);

// Index wins over value
$lookup_table_my_winning_scenarios = array(
    "X" => "C",     // Rock (X) beats Scissors (C)
    "Y" => "A",     // Paper (Y) beats Rock (A)
    "Z" => "B"      // Scissors (Z) beats Paper (Y)
);

$lookup_table_my_losing_scenarios = array(
    "A" => "Z",     // Rock (A) beats Scissors (Z)
    "B" => "X",     // Paper (B) beats Rock (X)
    "C" => "Y"      // Scissors (C) beats Paper (Y)
);

$i = 0;
$scores_part1 = array();
$scores_part2 = array();

foreach ($input as $k => $v) {
    $r = explode(" ", $v);

    // 0 = opponent
    // 1 = me

    // ---- Part 1 Scoring ----
    // shape score
    $scores_part1[$i] = $lookup_table_score[$r[1]];
    if ($lookup_table_shape[$r[0]] == $r[1]) {
        // Draw - 3 points
        $scores_part1[$i] += 3;
    } elseif ($lookup_table_my_winning_scenarios[$r[1]] == $r[0]) {
        // Win - 6 points
        $scores_part1[$i] += 6;
    }

    // ---- Part 2 Scoring ----
    if ($r[1] == "X") {
        // X = need to lose
        // Based on what the opponent played, what should my shape be in order to lose?
        $my_shape = $lookup_table_my_losing_scenarios[$r[0]];
        $scores_part2[$i] = $lookup_table_score[$my_shape];
    } elseif ($r[1] == "Z") {
        // Z = need to win
        // Based on what the opponent played, what should my shape be in order to win?
        $my_shape = array_flip($lookup_table_my_winning_scenarios)[$r[0]];
        $scores_part2[$i] = 6 + $lookup_table_score[$my_shape];
    } elseif ($r[1] == "Y") {
        // Y = Draw
        // Translate shape, get score; draw also results in 3 points
        $scores_part2[$i] = 3 + $lookup_table_score[$lookup_table_shape[$r[0]]];
    }

    $i++;
}

// Declare our answers
$answers = array(
    (int) array_sum($scores_part1),
    (int) array_sum($scores_part2)
);

print "Part One: What would your total score be if everything goes exactly according to your strategy guide? = ${answers[0]}\n";
print "Part Two: Following the Elf's instructions for the second column, what would your total score be if everything goes exactly according to your strategy guide? = ${answers[1]}\n";

if ($testMode) {
    $testAnswers = array_map('intval', (new ReadInputFile)->FromFile('example-input-answers.txt')->getInputArray());

    print "\n\nTESTS\n=====\n";

    for ($i = 0; $i < count($answers); $i++) {
        print "\tPart " . ($i + 1) . ": " . ($answers[$i] === $testAnswers[$i] ? "PASSED" : "failed! Expected Value: ${testAnswers[$i]}") . "\n";
    }
}
