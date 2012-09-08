<?php
namespace SimpleApi{
	class Logger {
		static function Log($s){
			if(DEBUG){
				$date = date('r');
				file_put_contents(LOG_FILE, "$date: $s", FILE_APPEND);
			}
		}
	}
}