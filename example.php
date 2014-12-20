<?php

/**
 * Include the curl lib first
 */
require_once 'curl.php';


/**
 * Make new Object
 */
$curl = new Curl();

/**
 * Prepare API Url
 */
$yql = 'https://query.yahooapis.com/v1/public/yql?q=show%20tables&format=json&diagnostics=true&callback=';


/**
 * Call using Curl Library
 * eg : $curl->get($yql);
 */
$response = $curl->get($yql);

/**
 * Got it!
 */
print_r($response);
