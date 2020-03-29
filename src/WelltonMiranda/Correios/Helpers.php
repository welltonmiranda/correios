<?php

if (!function_exists('cleanString')) {

	/**
	 * Limpa a string
	 *
	 * @param  $string
	 * @return string
	 */

	function cleanString($string) {

		$string = htmlentities($string, null, 'utf-8');

		$string = str_replace('&nbsp;', '', $string);

		$string = trim($string);

		$string = html_entity_decode($string, null, 'utf-8');

		$string = strip_tags($string);

		return $string;

	}

}