<?php
/**
 * Debug an variable
 * @param mixed $variable
 * @param string $info
 */
function d(mixed $variable, string $info = ''): void
{
	if(!Empty($info)) {
		print '<h2>' . $info . '</h2>';
	}
	print '<pre>';
	print_r($variable);
	print '</pre>';
}

/**
 * Debug & Die
 * @param mixed $variable
 * @param string $info
 */
function dd(mixed $variable, string $info = ''):never
{
	d($variable, $info);
	exit();
}
