# Using the PHP connector

The php connector currently removes the default debugging logic and replaces it with a logger to oohLaLog.
You only need set up the API key to be good to go.

### Global variables to set

* oohLaLogApiKey
* OLL_LOG_FILE _(optional)_
  * Defauls to /usr/local/php/error.log
* OLL_LOG_LEVEL
  * works like linux permissions
  * (1 = error,2 = warning,4 = info)
  * defaults to 3 (error,warning)
* OLL_PRINT_ERRORS _boolean_ (defaults to true)
 
### Usage
at the top of each script (or in a file that is included everywhere)

```php
$oohLaLogApiKey = 'XXX-XXXXXX-XXX-XX';
require('oohLaLogger.php');
```

anything that happens before the require will not be sent to oohlalog

## PHP Slim Log Writer

* add OohLaLoggerSlim.php to your project
* set OohLaLogWriter log writer as your log writer and add your API key (see example below)

```php
'log.writer' => new OohLaLog\OohLaLogWriter(array('apiKey' => 'XXX-XXXXXX-XXX-XX'))
```

* Get logging!


### Optional Attributes  
* messageFormat: Format of the log message. This can be set with the variables %label% (error label), %date% (iso time), and %message% (the message you send to the log writer).  
* quantityThreshold: Size of buffer that causes a flush the OohLaLog server. Default = 100.
* timeThreshold = Time waited(in milliseconds) before force flushing log buffer to the OohLaLogServer. Default = 10000.

*NOTE:* For both quantityThreshold and timeThreshold, lower numbers provide stronger guarantees but reduced performance.
 
```php
'log.writer' => new OohLaLog\OohLaLogWriter(array('apiKey' => 'XXX-XXXXXX-XXX-XX', 
                                                  'messageFormat' => "%label% - %message%",
                                                  'quantityThreshold' => 50),
                                                  'timeThreshold' => 5000)
```

### Limitations

* Currently uses the exec command to fork a curl process. You dont get a response, but it no longer blocks in php while running.
* The logging levels supported by OohLaLog are slightly different from thos available in Slim.  The table below shows how Slim logging levels translate to OohLaLog levels.

| Slim Levels          | OohLaLog Levels |
| -------------------- | --------------- |
| \Slim\Log::EMERGENCY | FATAL           |
| \Slim\Log::ALERT     | FATAL           |
| \Slim\Log::CRITICAL  | FATAL           |
| \Slim\Log::FATAL     | FATAL           |
| \Slim\Log::ERROR     | ERROR           |
| \Slim\Log::WARN      | WARN            |
| \Slim\Log::NOTICE    | INFO            |
| \Slim\Log::INFO      | INFO            |
| \Slim\Log::DEBUG     | DEBUG           |


