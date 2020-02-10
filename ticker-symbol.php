<?php
/**
* Plugin Name: Ticker Symbol
* Description: Plugin to display the ticker symbol using advanced custom fields symbol
* Version: 1.0
* Author: Nick McCally
* Author URI: https://nickmccally.com
**/

add_shortcode( 'ticker', 'ticker_symbol' );
add_filter('ticker_symbol', 'do_shortcode');

function ticker_symbol( $atts, $content = null ) {
  $a = shortcode_atts( array(
		'symbol' => '',
    'price' => 'true'
	), $atts );

  // Don't make a request without a symbol
  if($a['symbol']){
    $symbol = trim(strtoupper($a['symbol']));
    $price = "";

    $response = makeRequest("https://financialmodelingprep.com/api/v3/quote/{$symbol}")[0];

    // Only show price if it is enabled. price="false" will disable the price on ticker symbol
    if($response && $a['price'] == 'true'){
      if($response->change < 0)
        $price = "<span class='text-danger'><i class='fas fa-caret-down'></i> {$response->price}</span>";
      else
        $price = "<span class='text-success'><i class='fas fa-caret-up'></i> {$response->price}</span>";
    }
    $symbol = "(<a href='/featured-companies/{$response->symbol}/'>{$response->exhange}:{$response->symbol} {$price}</a>)";
  }

  return $symbol;
}


// Make curl request for API
function makeRequest($url, $callDetails = false)
{
  $ch = curl_init($url);

  // Set options
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

  // Execute curl handle add results to data return array.
  $result = curl_exec($ch);
  $returnGroup = json_decode($result);

  // If details of curl execution are asked for add them to return group.
  if ($callDetails) {
    $returnGroup = ['result' => $result,];
    $returnGroup['info'] = curl_getinfo($ch);
    $returnGroup['errno'] = curl_errno($ch);
    $returnGroup['error'] = curl_error($ch);
  }

  // Close cURL and return response.
  curl_close($ch);
  return $returnGroup;
}
