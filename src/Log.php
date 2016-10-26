<?php

Class Log {

	protected static $verbose = false;
	protected static $browser = true;


	public static function error() {
		self::logit('ERROR', func_get_args());
	}

	public static function info() {
		self::logit('INFO', func_get_args());
	}

	protected static function logit($type, $msgs) {
		ob_start();
	//debug_print_backtrace();
		foreach ($msgs as $msg) {
			print_r($msg);
			print_r("\n");
		}
		switch ($type) {
			case 'INFO':
				$logPath = DEFAULT_LOG_LOCATION;
				break;
			case 'ERROR':
				$logPath = ERROR_LOG_LOCATION;
				break;
		}
		$serverName = isset($_SERVER['SERVER_NAME'])?$_SERVER['SERVER_NAME']:"";
		$completeMsg = date('d-m-Y H:i:s', time()) ." ".$serverName.' [' . $type . ']: ' . ob_get_contents() . "\n" . "----------------------------------" . "\n";
		ob_end_clean();

		$fp = fopen(DEFAULT_LOG_LOCATION, 'a');
		fprintf($fp, "%s", $completeMsg);
		fclose($fp);

		if ($logPath != DEFAULT_LOG_LOCATION) {
			$fp = fopen($logPath, 'a');
			fprintf($fp, "%s", $completeMsg);
			fclose($fp);
		}
		if (self::$verbose) {
			if (self::$browser) {
				if ($type != 'INFO') {
					echo nl2br($completeMsg);
				}
			} else {
				echo $completeMsg;
			}
		}
	}

    public static function logRequest() {
		Log::info($_REQUEST);return;
        $password = null;
        $passwordSet = false;
        if(isset($_REQUEST['password'])){
            $password = $_REQUEST['password'];
            $passwordSet = true;
            unset($_REQUEST['password']);
        }
        Log::info($_REQUEST);
        if($passwordSet) {
            $_REQUEST['password'] = $password;
        }
    }
}

