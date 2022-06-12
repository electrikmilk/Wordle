<?php declare(strict_types=1);

const RESET = "\033[0m";

const BOLD = 1;
const DIM = 2;

const RED = 31;
const GREEN = 32;
const YELLOW = 33;
const CYAN = 36;

const GREEN_BG = 42;
const YELLOW_BG = 43;

// console input
function input(string $prompt): string
{
	echo style("$prompt: ", CYAN, BOLD);
	$input = fopen("php://stdin", "rb");
	do {
		$line = fgets($input);
	} while ($line === '');
	fclose($input);
	return trim($line);
}

// style console output
function style(string $str, ...$styles)
{
	return RESET . "\033[" . implode(';', $styles) . "m$str" . RESET;
}

// console output
function output(string $str, ...$styles)
{
	if ($styles) {
		echo RESET . "\033[" . implode(';', $styles) . "m$str" . RESET;
	} else {
		echo $str;
	}
	echo "\n";
}

// decide word
$words = json_decode(file_get_contents("words.json"), true);
shuffle($words);
$word = strtolower($words[0]);

output("Guess the random 5 letter word. You have 6 guesses.", CYAN);
output(style(' ', GREEN_BG) . ' = correct letter, correct position');
output(style(' ', YELLOW_BG) . ' = correct letter, wrong position');

$guesses = 6;
$split_word = str_split($word);
$used_letters = [];
$correct_letters = [];
$partially_correct_letters = [];
$best_guess = [0 => '', 1 => '', 2 => '', 3 => '', 4 => ''];
$alphabet = "abcdefghijklmnopqrstuvwxyz";
while ($guesses !== 0) {
	$alphabet_progress = "\n";
	foreach (str_split($alphabet) as $letter) {
		if (in_array($letter, $correct_letters, true)) {
			$alphabet_progress .= style($letter, GREEN, BOLD);
		} elseif (in_array($letter, $partially_correct_letters, true)) {
			$alphabet_progress .= style($letter, YELLOW, BOLD);
		} elseif (in_array($letter, $used_letters, true)) {
			$alphabet_progress .= style($letter, DIM);
		} else {
			$alphabet_progress .= $letter;
		}
	}
	output($alphabet_progress);
	if (count($best_guess)) {
		$progress = "";
		foreach ($best_guess as $letter) {
			if ($letter === '') {
				$progress .= '-';
				continue;
			}
			$progress .= style($letter, GREEN, BOLD);
		}
		output($progress);
	}
	$input = strtolower(input("Guess ($guesses/6)"));
	$input_len = strlen($input);
	preg_match_all('/[A-z]/', $input, $matches);
	if (!$matches) {
		output("Letters only.", RED, BOLD);
		continue;
	}
	if ($input_len !== 5) {
		output("5 character words only.", RED, BOLD);
		continue;
	}
	$pspell = pspell_new('en');
	if (!pspell_check($pspell, $input)) {
		output("Not a word: $input.", RED, BOLD);
		continue;
	}
	$split_input = str_split($input);
	$result = "";
	for ($i = 0; $i < 5; $i++) {
		$current_letter = $split_input[$i];
		if ($current_letter === $split_word[$i]) {
			if (in_array($current_letter, $partially_correct_letters, true)) {
				unset($partially_correct_letters[$current_letter]);
			}
			$correct_letters[] = $current_letter;
			$best_guess[$i] = $current_letter;
			$result .= style($current_letter, GREEN, BOLD);
		} elseif (in_array($current_letter, $split_word, true) !== false) {
			$partially_correct_letters[] = $current_letter;
			$result .= style($current_letter, YELLOW, BOLD);
		} else {
			$used_letters[] = $current_letter;
			$result .= $current_letter;
		}
	}
	output($result);
	if ($input === $word) {
		$guesses = 0;
		output('Correct!', GREEN, BOLD);
		die;
	}
	--$guesses;
}
output(style("\nAnswer: ", BOLD) . style($word, GREEN, BOLD));