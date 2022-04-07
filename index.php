<?php declare(strict_types=1);

// get console input
function read_input(): string
{
	$input = fopen("php://stdin", "rb");
	do {
		$line = fgets($input);
	} while ($line == '');
	fclose($input);
	return $line;
}

// decide word
$words = json_decode(file_get_contents("words.json"), true);
shuffle($words);
$word = $words[0];

$guesses = 6;
$split_word = str_split($word);
while ($guesses !== 0) {
	echo "Guess $guesses (5 characters):";
	$input = read_input();
	$input_len = strlen(trim($input));
	if ($input_len !== 5) {
		echo "Try again! $input_len characters. 5 characters only.\n";
		continue;
	}
	$split_input = str_split($input);
	$result = strtolower($input);
	foreach ($split_input as $in) {
		if(in_array($in,$split_word) !== false) {
			$result = str_replace($in,strtoupper($in),$result);
		}
	}
	echo "$result\n";
	--$guesses;
}
echo "Answer: $word\n";