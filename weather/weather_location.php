<?php

/*

Summary: Uses the Wunderground API (http://www.wunderground.com/weather/api/) to resolve a text location
to a Wunderground weather location.

Expects a GET variable 'search'.

RETURNS:
JSON with the fields:
  'error' (true/false)
  'error_msg' (a text description of an error)
  'matches' (an array of possible matching locations with 'name' and 'loc' (Wunderground ID) values)

*/

	header("Content-Type: text/plain");

	//Change this
	$_GET['search'] = 'Boston, MA';

	$response = array();
	$response['error'] = false;
	$response['error_msg'] = '';

	//Accepts cities, airport codes, etc.
	$url = 'http://autocomplete.wunderground.com/aq?format=json&query='.urlencode($_GET['search']);

	$autocomplete = curl_return($url);
	//print_r($autocomplete); die();

	if (!isset($autocomplete->RESULTS)) {
		$response['error'] = true;
		$response['error_msg'] = 'Unknown server error.';
	} elseif (!count($autocomplete->RESULTS)) {
		$response['error'] = true;
		$response['error_msg'] = 'No matching location.';
	} else {
		foreach ($autocomplete->RESULTS as $result) {
				$display_name = $result->name;
				if (isset($result->c) && $result->c == 'US') $display_name = $display_name.', USA';
				$response['matches'][] = array('name' => $display_name, 'loc' => $result->l);
		}
	}

	print_r(json_encode($response)); die();

	function curl_return($url,$json = TRUE) {
		
		$ch = curl_init($url);		
		
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, TRUE);
		$curl_result = curl_exec($ch);
		
		curl_close($ch);

		if (!$json) {
			return $curl_result;
		}
		return json_decode($curl_result);

	}
?>