<?php

define('LARAVEL_START', microtime(true));


/**
 * Log errors: Development purposes only.
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('max_execution_time', 300); // 300 seconds, because some analysis can take up to 1 or 2 minutes.

/**
 * Use the online API?
 *
 * TRUE : Connect to the Stanford CoreNLP server online
 * FALSE: Uses Java version
 */
define('ONLINE_API', TRUE);  // since Adapter version 5.0.0 FALSE by default.

/**
 * Stanford API URL configuration
 */
define('ONLINE_URL' , 'http://nlp.stanford.edu:8080/corenlp/process?outputFormat=json&Process=Submit&input=');

/**
 * Guzzle is used to make HTTP calls to the CoreNLP server.
 *
 * If true: HTTP calls are used (recommended)
 * If false: cURL command line is used
 */
define('USE_GUZZLE', TRUE);

/**
 * Java version configuration
 */
define('CURLURL' , 'http://localhost:9000/');

// used for CoreNLP version 3.7.0
define('CURLPROPERTIES' , '%22prettyPrint%22%3A%22true%22');

// if you want specific annotators, you can do it like this:
// define('CURLPROPERTIES' , '%22annotators%22%3A%22tokenize%2Cregexner%2Cparse%2Cdepparse%2Cpos%2Clemma%2Cmention%2Copenie%2Cner%2Ccoref%2Ckbp%22%2C%22prettyPrint%22%3A%22true%22');


/*
|--------------------------------------------------------------------------
| Register The Composer Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader
| for our application. We just need to utilize it! We'll require it
| into the script here so that we do not have to worry about the
| loading of any our classes "manually". Feels great to relax.
|
*/

require __DIR__.'/../vendor/autoload.php';
