<?php
	function ollog_error_handler($errorno, $errormsg, $filename, $linenum, $vars)
	{
		//min time between calls
		$ollThrottle = 2000;
		//max time between calls
		//$ollPurgeTime = 10000;
		$ollThreshold = 1;
		$logThis = false;

		if (!isset($GLOBALS["OLL_LAST_CALL"])){
			$GLOBALS["OLL_LAST_CALL"] = 0;
		}
		if (!isset($GLOBALS["OLL_PAYLOAD"])){
			$GLOBALS["OLL_PAYLOAD"] = array( logs => array());
		}
		if (!isset($GLOBALS["OLL_LOG_FILE"])){
			$GLOBALS["OLL_LOG_FILE"] = "/usr/local/php/error.log";
		}
		if (!isset($GLOBALS["OLL_LOG_LEVEL"])){
			$GLOBALS["OLL_LOG_LEVEL"] = 3;
		}
		if (!isset($GLOBALS["OLL_PRINT_ERRORS"])){
			$GLOBALS["OLL_PRINT_ERRORS"] = true;
		}


		$timestamp = 0;
		if ($PHP_VERSION_ID < 50300){
			$date = new DateTime();
			$timestamp = $date->format('U');
			$timestamp *= 1000;
		}
		else {
			$timestamp = date_timestamp_get(date_create()) * 1000;
		}

		// define an assoc array of error string
		// in reality the only entries we should
		// consider are E_WARNING, E_NOTICE, E_USER_ERROR,
		// E_USER_WARNING and E_USER_NOTICE
		$errortype = array (
		E_ERROR              => 'Error',
		E_WARNING            => 'Warning',
		E_PARSE              => 'Parsing Error',
		E_NOTICE             => 'Notice',
		E_CORE_ERROR         => 'Core Error',
		E_CORE_WARNING       => 'Core Warning',
		E_COMPILE_ERROR      => 'Compile Error',
		E_COMPILE_WARNING    => 'Compile Warning',
		E_USER_ERROR         => 'User Error',
		E_USER_WARNING       => 'User Warning',
		E_USER_NOTICE        => 'User Notice',
		E_STRICT             => 'Runtime Notice',
		E_RECOVERABLE_ERROR  => 'Catchable Fatal Error'
		);

		$level = array (
		E_ERROR              => 'ERROR',
		E_WARNING            => 'WARN',
		E_PARSE              => 'ERROR',
		E_NOTICE             => 'INFO',
		E_CORE_ERROR         => 'ERROR',
		E_CORE_WARNING       => 'WARN',
		E_COMPILE_ERROR      => 'ERROR',
		E_COMPILE_WARNING    => 'WARN',
		E_USER_ERROR         => 'ERROR',
		E_USER_WARNING       => 'WARN',
		E_USER_NOTICE        => 'INFO',
		E_STRICT             => 'INFO',
		E_RECOVERABLE_ERROR  => 'ERROR'
		);

		// set of errors for which a var trace will be saved
		if ( $level[$errorno] == 'ERROR' && ( $GLOBALS["OLL_LOG_LEVEL"] == 1 ||
			$GLOBALS["OLL_LOG_LEVEL"] == 3 ||
			$GLOBALS["OLL_LOG_LEVEL"] == 5 ||
			$GLOBALS["OLL_LOG_LEVEL"] == 7 ) )
		{
			$logThis = true;
		}
		if ( $level[$errorno] == 'WARN' && ( $GLOBALS["OLL_LOG_LEVEL"] == 2 ||
			$GLOBALS["OLL_LOG_LEVEL"] == 3 ||
			$GLOBALS["OLL_LOG_LEVEL"] == 6 ||
			$GLOBALS["OLL_LOG_LEVEL"] == 7 ) )
		{
			$logThis = true;
		}
		if ( $level[$errorno] == 'INFO' && ( $GLOBALS["OLL_LOG_LEVEL"] == 4 ||
			$GLOBALS["OLL_LOG_LEVEL"] == 5 ||
			$GLOBALS["OLL_LOG_LEVEL"] == 6 ||
			$GLOBALS["OLL_LOG_LEVEL"] == 7 ) )
		{
			$logThis = true;
		}
		if ($logThis){
			$log = array (
				level => $level[$errorno],
				message => $errormsg,
				category => $errortype[$errorno],
				timestamp => $timestamp,
				agent => 'PHP',
				details => 'File Name: ' . $filename . '; Line Number: ' . $linenum . ';'
				);

			//add to the log file (just in case something goes wrong)
			error_log($log, 3, $GLOBALS["OLL_LOG_FILE"]);

			// add log to the global payload
			array_push($GLOBALS["OLL_PAYLOAD"]["logs"],$log);


			//$timeSince = $timestamp - $GLOBALS["OLL_LAST_CALL"];
			//if our requirements are met
			//if (sizeof($GLOBALS["OLL_PAYLOAD"]) >= $ollThreshold || $timeSince > $ollThrottle){
				sendLog();
				if (isset($GLOBALS["OLL_PRINT_ERRORS"]) && $GLOBALS["OLL_PRINT_ERRORS"]){
					print_r($GLOBALS["OLL_PAYLOAD"]);
				}
			//}
		}
	}
//function that sends the logs to oohlalog
	function sendLog(){
		//url config
		$ollHost = 'oohlalog.com';
		$ollPath = '/api/logging/save.json';
		$ollPort = '80';
		$url = 'http://' . $ollHost . ':' . $ollPort . $ollPath . '?apiKey=' . $GLOBALS["oohLaLogApiKey"];

		//only send something out if there are logs in the payload
		if (isset($GLOBALS["oohLaLogApiKey"])){
			if (sizeof($GLOBALS["OLL_PAYLOAD"]) > 0){
				$payload = json_encode($GLOBALS["OLL_PAYLOAD"]);

				$cmd = "curl -X POST -H 'Content-Type: application/json'";
				$cmd.= " -d '" . $payload . "' " . "'" . $url . "'";
				$cmd .= " > /dev/null 2>&1 &";

				exec($cmd, $output, $exit);
				$GLOBALS["OLL_PAYLOAD"] = NULL;
			}
		}
		else {
			echo "Your api key set for oohLaLog. Make sure the variable 'oohLaLogApiKey' is set in the global scope.";
		}

	}

	//make sure we want to actually do this
	if(isset($oohLaLogEnabled) && $oohLaLogEnabled == true){
		//dont set the custom handler if there is no api key
		if (isset($GLOBALS["oohLaLogApiKey"])) {
			$old_error_handler = set_error_handler("ollog_error_handler");
		}
		else {
			echo "Your api key set for oohLaLog. Make sure the variable 'oohLaLogApiKey' is set in the global scope.";
		}
	}
?>
