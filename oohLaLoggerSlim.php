<?php

namespace OohLaLog;

class OohLaLogWriter
{
    protected $settings;

    public function __construct($settings = array())
    {

        //Merge user settings
        $this->settings = array_merge(array(
            'logFile' => '/usr/local/php/error.log',
            'logLevel' => \Slim\Log::ERROR,
            'printErrors' => true,
            'host' => 'oohlalog.com',
            'path' => '/api/logging/save.json',
            'port' => '80',
            'messageFormat' => "%label% - %message%"
        ), $settings);

        $this->payload = array( logs => array());
    }

    public function write($object, $level){
        //Determine label
         $label = 'DEBUG';
         switch ($level) {
             case \Slim\Log::FATAL:
                 $label = 'FATAL';
                 break;
             case \Slim\Log::ERROR:
                 $label = 'ERROR';
                 break;
             case \Slim\Log::WARN:
                 $label = 'WARN';
                 break;
             case \Slim\Log::INFO:
                 $label = 'INFO';
                 break;
         }

         if ($level <= $this->settings['logLevel'] ){
         $message = str_replace(array(
             "%label%",
             "%date%",
             "%message%"
         ), array(
             $label,
             date("c"),
             (string)$object
         ), $this->settings["messageFormat"]);
             $log = array (
                 level => $label,
                 message => $message,
                 category => $label,
                 timestamp => time() * 1000,
                 agent => 'PHP',
                 details => $message
                 );

             array_push($this->payload['logs'],$log);

             $this->sendLogs();
         }

    }

    private function sendLogs() {
        $url = 'http://' . $this->settings['host'] . ':' . $this->settings['port'] . $this->settings['path'] . '?apiKey=' . $this->settings["apiKey"];
        //only send something out if there are logs in the payload
         if (isset($this->settings["apiKey"])){
             if (sizeof($this->payload) > 0){
                 $payload = json_encode($this->payload);

                 $cmd = "curl -X POST -H 'Content-Type: application/json'";
                 $cmd.= " -d '" . $payload . "' " . "'" . $url . "'";
                 $cmd .= " > /dev/null 2>&1 &";

                 exec($cmd, $output, $exit);
                 $this->payload = [];
             }
         }
         else {
             echo "Your api key is not set for oohLaLog. Make sure the variable 'apiKey' is set in the global scope.";
         }
    }
}
