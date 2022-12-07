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
 *  $ cat example-input.txt | php -f Day-05-Solution-PHP.php
 *
 *  # Read from default `input.txt`
 *  $ php -f Day-05-Solution-PHP.php
 *
 *  # Run tests on example data
 *  $ php -f Day-05-Solution-PHP.php test
 * ```
 */

class ReadInputFile
{
    public $input = array();

    public function FromSTDIN($fallbackFromFile = true)
    {
        stream_set_blocking(STDIN, 0);

        $stdin = array();

        while (($f = fgets(STDIN)) !== false) {
            $stdin[] = trim($f, "\r\n");
        }

        if (!empty($stdin)) {
            $this->input = $stdin;
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
            $this->input = explode("\n", file_get_contents($filename));
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

$found_separator = false;
$raw_stacks = array();
$stacks = array();
$moves = array();

function parse_move($move)
{
    preg_match("/move\s(\d+)\sfrom\s(\d+)\sto\s(\d+)/", $move, $m);

    return array(
        'move' => (int) $m[1],
        'from' => (int) $m[2],
        'to'   => (int) $m[3]
    );
}

/**
 * Process the input file
 */
foreach ($input as $k => $v) {
    if (!$found_separator) {
        if (empty($v)) {
            $found_separator = true;
            continue;
        } else {
            $raw_stacks[$k] = array_map('trim', array_map(function ($r) {
                return preg_replace("/\[|\]/", "", $r);
            }, str_split($v, 4)));
            continue;
        }
    } else {
        array_push($moves, parse_move($v));
        continue;
    }
}

/*
    Use array_pop($stacks) to get (and remove) the last key.
    Last key contains an array of values representing the stack number.
*/
$stack_ids = array_values(array_pop($raw_stacks));

/**
 * Process initial state of the stack
 */
foreach ($raw_stacks as $k => $v) {
    foreach ($v as $i => $n) {
        // k is the row; we don't need it here
        // i is going to be the stack id (+1 so we don't start at 0)
        $i += 1;
        // n is the crate
        if (!empty($n)) {
            $stacks[$i][] = $n;
        }
    }
}

ksort($stacks);

// Garbage
unset($raw_stacks);

// Copy initial stacks for part 2
$stacks2 = $stacks;

/**
 * Process each move
 */
function move_crates(&$stack, &$moves, $crane = "9000")
{
    foreach ($moves as $m) {
        switch ($crane) {
            case "9001":
                // CrateMover 9001 = move many at a time
                $spliced = array_reverse(array_splice($stack[$m['from']], 0, $m['move']));
                break;
            case "9000":
            default:
                // CrateMover 9000 = move 1 at a time
                $spliced = array_splice($stack[$m['from']], 0, $m['move']);
                break;
        }

        $stack[$m['to']] = array_reverse(
            array_merge(
                array_reverse($stack[$m['to']]),
                $spliced
            )
        );
    }

    return $stack;
}

function get_top_crate(&$stack, &$stack_ids)
{
    $top_crates = "";
    foreach ($stack_ids as $k => $v) {
        if (!empty($stack[$v]) && !empty($stack[$v][0])) {
            $top_crates .= $stack[$v][0];
        }
    }
    return $top_crates;
}

$stacks  = move_crates($stacks, $moves, "9000");
$stacks2 = move_crates($stacks2, $moves, "9001");

$part1_answer = get_top_crate($stacks, $stack_ids);
$part2_answer = get_top_crate($stacks2, $stack_ids);

// Declare our answers
$answers = array(
    (string) $part1_answer,
    (string) $part2_answer,
);

print "Part One: After the rearrangement procedure completes, what crate ends up on top of each stack? = ${answers[0]}\n";
print "Part Two: After the rearrangement procedure completes, what crate ends up on top of each stack? = ${answers[1]}\n";

if ($testMode) {
    $testAnswers = (new ReadInputFile)->FromFile('example-input-answers.txt')->getInputArray();

    print "\n\nTESTS\n=====\n";

    for ($i = 0; $i < count($answers); $i++) {
        print "\tPart " . ($i + 1) . ": " . ($answers[$i] === $testAnswers[$i] ? "PASSED" : "failed! Expected Value: ${testAnswers[$i]}") . "\n";
    }
}
