<?php

/*

Summary: Uses the Wunderground API (http://www.wunderground.com/weather/api/) to look for dramatic
weather changes/extremes for a supplied location.  Expects a file 'wunderground.php' which defines
the constant 'WUNDERGROUND_API_KEY'.

Reports back text alerts if:

  -the average temperature is expected to increase or decrease more than a threshold amount in the next 3 days
  -today's high or low is above or below the record high or low for the historical date

Expects a GET variable 'loc', which is a Wunderground API location.  This can be any accepted format:
  '/q/zmw:94101.1.99999' (Wunderground ID)
  '/q/France/Paris'
  '/q/90210' (ZIP code)
  '/q/LAX' (Airport code)
  '/q/37.776289,-122.395234' (Lat/Long)

RETURNS:
JSON with the fields:
  'error' (true/false)
  'error_msg' (a text description of an error)
  'alerts' (an array of text alerts, empty if no alerts)

*/

	//Need a Wunderground API key
	require_once('wunderground.php');

	//degrees F that the average temperature must be expected to change to trigger an alert
	$tolerance = 8;

	header("Content-Type: text/plain");

	//Initialize the array for a JSON response
	$response = array();
	$response['error'] = false;
	$response['error_msg'] = '';
	$response['alerts'] = array();

	//Supplied Wunderground location code
	$loc = $_GET['loc'];

	//Call for 3-day forecast
	$url = 'http://api.wunderground.com/api/'.WUNDERGROUND_API_KEY.'/forecast'.$loc.'.json';
	$forecast = curl_return($url);

	//If there is no forecast returned, something went wrong
	if (!isset($forecast->forecast->simpleforecast->forecastday)) {
		$response['error'] = true;
		$response['error_msg'] = 'Unknown server error (forecast).';
	} else {

		$days = $forecast->forecast->simpleforecast->forecastday;

		//current daily average
		$current_avg = ($days[0]->high->fahrenheit + $days[0]->low->fahrenheit)/2;
		$current_high = $days[0]->high->fahrenheit;
		$current_low = $days[0]->low->fahrenheit;

		//for each of the next 3 days...
		foreach ($days as $ind => $day) {			
			switch ($ind) {
				case 0:
					continue;
					break;
				case 1:
					$day_text = 'tomorrow';
					break;
				case 2:
					$day_text = 'the day after tomorrow';
					break;				
				case 3:
					$day_text = 'in two days';
					break;
				default:
					continue;
					break;
			}
			
			//Expected average temperature
			$new_avg = ($day->high->fahrenheit + $day->low->fahrenheit)/2;
			
			//If it's more than [tolerance] degrees F higher than today's average, alert
			if ($new_avg - $current_avg > $tolerance) {
				$response['alerts'][] = 'The average local temperature is expected to go up '.round($new_avg - $current_avg).' degrees F '.$day_text;
			
			//If it's more than [tolerance] degrees F lower than today's average, alert
			} elseif ($current_avg - $new_avg > $tolerance) {
				$response['alerts'][] = 'The average local temperature is expected to go down '.round($current_avg - $new_avg).' degrees F '.$day_text;
			}

		}

		$url = 'http://api.wunderground.com/api/'.WUNDERGROUND_API_KEY.'/almanac'.$loc.'.json';

		//Check the historical records for today
		$almanac = curl_return($url);

		//If there is no almanac returned, something is wrong
		if (!isset($almanac->almanac)) {
			$response['error'] = true;
			$response['error_msg'] = 'Unknown server error (almanac).';
		} else {
			$almanac = $almanac->almanac;

			//If today's high is at/near the record high, alert
			if ($current_high > $almanac->temp_high->record->F) {
				$response['alerts'][] = 'Today\'s high of '.$current_high.' degrees F is greater than the record high for today\'s date of '.$almanac->temp_high->record->F.' degrees F in '.$almanac->temp_high->recordyear;
			} elseif ($current_high == $almanac->temp_high->record->F) {
				$response['alerts'][] = 'Today\'s high of '.$current_high.' degrees F is equal to the record high for today\'s date of '.$almanac->temp_high->record->F.' degrees F in '.$almanac->temp_high->recordyear;
			} elseif ($current_high > $almanac->temp_high->record->F - 2) {
				$response['alerts'][] = 'Today\'s high of '.$current_high.' degrees F is near the record high for today\'s date of '.$almanac->temp_high->record->F.' degrees F in '.$almanac->temp_high->recordyear;
			}

			//If today's low is at/near the record low, alert
			if ($current_low  <$almanac->temp_low->record->F) {
				$response['alerts'][] = 'Today\'s low of '.$current_low.' degrees F is less than the record low for today\'s date of '.$almanac->temp_low->record->F.' degrees F in '.$almanac->temp_low->recordyear;
			} elseif ($current_low == $almanac->temp_low->record->F) {
				$response['alerts'][] = 'Today\'s low of '.$current_low.' degrees F is equal to the record low for today\'s date of '.$almanac->temp_low->record->F.' degrees F in '.$almanac->temp_low->recordyear;
			} elseif ($current_low < $almanac->temp_low->record->F - 2) {
				$response['alerts'][] = 'Today\'s low of '.$current_low.' degrees F is near the record low for today\'s date of '.$almanac->temp_low->record->F.' degrees F in '.$almanac->temp_low->recordyear;
			}		

		}
	}

	print_r($response);

	//return the curl result for a specified URL, default to returning JSON as an array
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