<?php
class YLogger
{
    const LOG_LEVEL_NONE    = 0x00;
    const LOG_LEVEL_ERROR   = 0x01;
    const LOG_LEVEL_WARN    = 0x02;
    const LOG_LEVEL_ACCESS  = 0x04;
    const LOG_LEVEL_INFO    = 0x08;
    const LOG_LEVEL_DEBUG   = 0x10;
    const LOG_LEVEL_ALL     = 0xFF;
    
    const LOG_SPLIT_NONE = 0;
    const LOG_SPLIT_DAY  = 1;
    const LOG_SPLIT_HOUR = 2;

    /**
     * @var array 日志级别字典
     */
    public static $levels = array(
        self::LOG_LEVEL_NONE    => 'NONE',
        self::LOG_LEVEL_ERROR   => 'ERROR',
        self::LOG_LEVEL_WARN    => 'WARN',
        self::LOG_LEVEL_ACCESS  => 'ACCESS',
        self::LOG_LEVEL_INFO    => 'INFO',
        self::LOG_LEVEL_DEBUG   => 'DEBUG',
        self::LOG_LEVEL_ALL     => 'ALL',
    );

    protected $level;

    protected $log_split;

    protected $log_file;

    protected $log_path;

    protected $logid;

    protected $start_time;

    private static $instance = null;

    private function __construct() {/*{{{*/
        $this->logid     = self::genLogId();             //日志ID
        $this->start_time = defined('YC_START_TIME') ? YC_START_TIME : microtime(true) * 1000; //开始时间
        $this->level      = isset(YConfig::$log_conf['level']) ? (int)YConfig::$log_conf['level'] : 0x07; //日志级别
        $this->log_split  = isset(YConfig::$log_conf['split']) ? (int)YConfig::$log_conf['split'] : self::LOG_SPLIT_DAY; //日志切分
        $this->log_file   = isset(YConfig::$log_conf['logfile']) ? YConfig::$log_conf['logfile'] : dirname(__DIR__) . '/logs/yclient.log'; //日志文件
        $this->log_path   = dirname($this->log_file); //默认日志目录
        $this->other_log_files = isset(YConfig::$log_conf['logfile']) ? YConfig::$log_conf['others'] : null;   //其他日志文件

        if(!is_dir($this->log_path)) {
            mkdir($this->log_path, 0777, true);
        }
    }/*}}}*/

    public static function getInstance() {/*{{{*/
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }/*}}}*/

    public static function debug($errlog, $errorno = 0, $args = null, $depth = 0) {/*{{{*/
        $instance = self::getInstance();
        return $instance->writeLog(self::LOG_LEVEL_DEBUG, $errlog, $errorno, $args, $depth + 1);
    }/*}}}*/

    public static function info($errlog, $errorno = 0, $args = null, $depth = 0) {/*{{{*/
        $instance = self::getInstance();
        return $instance->writeLog(self::LOG_LEVEL_INFO, $errlog, $errorno, $args, $depth + 1);
    }/*}}}*/

    public static function warn($errlog, $errorno = 0, $args = null, $depth = 0) {/*{{{*/
        $instance = self::getInstance();
        return $instance->writeLog(self::LOG_LEVEL_WARN, $errlog, $errorno, $args, $depth + 1);
    }/*}}}*/

    public static function error($errlog, $errorno = 0, $args = null, $depth = 0) {/*{{{*/
        $instance = self::getInstance();
        return $instance->writeLog(self::LOG_LEVEL_ERROR, $errlog, $errorno, $args, $depth + 1);
    }/*}}}*/

    public static function access($errlog, $errorno = 0, $args = null, $depth = 0) {/*{{{*/
        $instance = self::getInstance();
        return $instance->writeLog(self::LOG_LEVEL_ACCESS, $errlog, $errorno, $args, $depth + 1);
    }/*}}}*/

    public static function other($key, $errlog, $args = null) {/*{{{*/
        $instance = self::getInstance();
        return $instance->writeOtherLog($key, $errlog, $args);
    }/*}}}*/

    public static function getClientIP() {/*{{{*/
        $ip = "";
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '127.0.0.1') {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];

            $pos = strpos($ip, ',');
            if ($pos > 0) {
                $ip = substr($ip, 0, $pos);
            }
        } elseif (isset($_SERVER['HTTP_CLIENTIP'])) {
            $ip = $_SERVER['HTTP_CLIENTIP'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
       
        if("" != $ip) {
            $pos = strpos($ip, ':');
            if ($pos > 0) {
                $ip = substr($ip, 0, $pos);
            }
            $ip = trim($ip);
        } else {
            $ip = '127.0.0.1';
        }

        return $ip;
    }/*}}}*/

    private static function genLogId() {/*{{{*/
        return (crc32(gethostname()) & 0x7FFFFFF | 0x8000000) . ((microtime(true) * 10000) & 0x7FFFFFF | 0x8000000);
    }/*}}}*/

    public function writeLog($level, $errlog, $errorno = 0, $args = null, $depth = 0) {/*{{{*/
        if (($level&$this->level) == 0) {
            return;
        }

        $level_name    = self::$levels[$level];
        $log_file = $this->getLogFile($level_name);

        $debug_trace = debug_backtrace();
        if ($depth >= count($debug_trace)) {
            $depth = count($debug_trace) - 1;
        }
        $filename = basename($debug_trace[$depth]['file']);
        $line     = intval($debug_trace[$depth]['line']);

        $args_val = '';
        if (is_array($args) && count($args) > 0) {
            foreach ($args as $k => $v) {
                $args_val .= sprintf('%s[%s] ', $k, $v);
            }
        }

        $time_used = microtime(true)*1000 - $this->start_time;

        $str = sprintf(
            "%s:%s [%s:%d] errno[%d] ip[%s] logId[%u] uri[%s] time_used[%d] %s%s\n",
            $level_name,
            date('m-d H:i:s:', time()),
            $filename, 
            $line, 
            $errorno,
            self::getClientIP(),
            $this->logid,
            isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '',
            $time_used, 
            $args_val, 
            $errlog
        );

        return file_put_contents($log_file, $str, FILE_APPEND);
    }/*}}}*/

    public function writeOtherLog($key, $errlog, $args = null) {/*{{{*/
		$log_file = $this->getOtherLogFile($key);

        if(false === $log_file) {
            return;
        }
		
        $args_val = '';
        if( is_array($args) && count($args) > 0) {
            foreach($args as $k => $value) {
                $args_val .= $k . "[$value] ";
            }
        }

        $str = sprintf( "%s: time[%s] ip[%s] logId[%u] uri[%s] %s%s\n",
            $key,
            date('m-d H:i:s:', time()),
            self::getClientIP(),
            $this->logid,
            isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '',
            $args_val,
            $errlog
        );

        return file_put_contents($log_file, $str, FILE_APPEND);
    }/*}}}*/

    private function getLogFile($level_name) {/*{{{*/
        $log_file = $this->log_file;

        if ($level_name != "ACCESS") {
            $log_file .= '.' . strtolower($level_name);
        }

        if ($this->log_split == self::LOG_SPLIT_DAY) {
            $log_file .= '.' . date("Ymd");
        } elseif ($this->log_split == self::LOG_SPLIT_HOUR) {
            $log_file .= '.' . date("YmdH");
        }

        return $log_file;
    }/*}}}*/

    private function getOtherLogFile($key) {/*{{{*/
        if(isset($this->other_log_files[$key])) {
            $log_file = $this->other_log_files[$key];
        } else {
            $log_file = $this->log_path . "/" . $key . ".log";
        }

		if ($this->log_split == self::LOG_SPLIT_DAY) {
            $log_file .= '.' . date("Ymd");
        } elseif ($this->log_split == self::LOG_SPLIT_HOUR) {
            $log_file .= '.' . date("YmdH");
        }

		return $log_file;;
	}/*}}}*/
}

