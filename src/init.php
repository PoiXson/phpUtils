<?php
/*
 * PoiXson phpUtils - PHP Utilities Library
 * @copyright 2004-2016
 * @license GPL-3
 * @author lorenzo at poixson.com
 * @link http://poixson.com/
 */
# init 1 - startup
# init 2 - functions
# init 3 - defines
# init 4 - configs
# init 5 - debug
# init 6 - globals
namespace pxn\phpUtils;



########################
##                    ##
##  init 1 - startup  ##
##                    ##
########################



if (\defined('pxn\\phpUtils\\inited')) {
	return FALSE;
}
define('pxn\\phpUtils\\inited', TRUE);



class init {
	public static function init() {}
}



// default error reporting
{
	$isShell = System::isShell();
	\error_reporting(\E_ALL);
	\ini_set('display_errors', 'On');
	\ini_set('html_errors',    $isShell ? 'Off' : 'On');
	\ini_set('log_errors',     'On');
	\ini_set('error_log',      $isShell ? '/var/log/php_shell_error' : 'error_log');
}

// php version 5.6 required
if (\PHP_VERSION_ID < 50600) {
	echo '<p>PHP 5.6 or newer is required!</p>'; exit(1);
}

// atomic defines
if (\defined('pxn\\phpUtils\\INDEX_DEFINE')) {
	echo '<h2>Unknown state! init.php already loaded?</h2>';
	exit(1);
}
if (\defined('pxn\\phpUtils\\PORTAL_LOADED')) {
	echo '<h2>Unknown state! Portal already loaded?</h2>';
	exit(1);
}
if (!\function_exists('mb_substr')) {
	echo '<h2>mbstring library not installed?</h2>';
	exit(1);
}
\define('pxn\\phpUtils\\INDEX_DEFINE', TRUE);

// timezone
//TODO: will make a config entry for this
try {
	$zone = @date_default_timezone_get();
	if ($zone == 'UTC') {
		@date_default_timezone_set(
			'America/New_York'
		);
	} else {
		@date_default_timezone_set(
			@date_default_timezone_get()
		);
	}
	unset($zone);
} catch (\Exception $ignore) {}



##########################
##                      ##
##  init 2 - functions  ##
##                      ##
##########################



//$phpUtils_logger = NULL;
//if (!\function_exists('log')) {
//	function log() {
//		global $phpUtils_logger;
//		if ($phpUtils_logger == NULL)
//TODO:
//			$phpUtils_logger = new logger();
//		return $phpUtils_logger;
//	}
//}
//class logger {
//TODO:
//}



//// php session
//if (function_exists('session_status'))
//	if (session_status() == PHP_SESSION_DISABLED){
//	echo '<p>PHP Sessions are disabled. This is a requirement, please enable this.</p>';
//	exit;
//}
//session_init();



//// init php sessions
//private static $session_init_had_run = FALSE;
//public static function session_init() {
//	if (self::$session_init_had_run) return;
//	\session_start();
//	self::$session_init_had_run = TRUE;
//}



//function addlog($text){global $config,$pathroot;
//if (mb_substr($config['log file'],-4)!='.txt'){die('error in log file var');}
//$fp=@fopen($pathroot.$config['log file'],'a') or die('failed to write log');
//fwrite($fp,date('Y-m-d H:i:s').' - '.trim($text)."\r\n");
//fclose($fp);
//}



// dump()
function dump($var, $msg=NULL) {
	if (System::isShell()) {
		if (empty($msg)) {
			echo "--DUMP--\n";
		} else {
			$msg = \str_replace(' ', '-', $msg);
			echo "--DUMP-{$msg}--\n";
		}
		\var_dump($var);
		echo "--------\n";
	} else {
		$CRLF = "\n";
		echo '<pre style="color: black; background-color: #dfc0c0; padding: 10px;">';
		\var_dump($var);
		echo '</pre>'.$CRLF;
	}
	@\ob_flush();
}
// d()
function d($var, $msg=NULL) {
	dump($var, $msg);
}
// dd()
function dd($var, $msg=NULL) {
	dump($var, $msg);
	ExitNow(1);
}



// exit functions
function ExitNow($code=1) {
	// be sure captured buffer is dumped when in web mode
	{
		$app = \pxn\phpUtils\app\App::peak();
		if ($app != NULL) {
			$app->terminating();
		}
		unset($app);
	}
	// set rendered
	if (\class_exists('pxn\\phpPortal\\WebApp')) {
		$app = \pxn\phpPortal\WebApp::peak();
		if ($app != NULL) {
			$app->setRendered();
		}
	}
	// exit code
	if ($code !== NULL) {
		exit( ((int) $code) );
	}
	exit(0);
}
function fail($msg=NULL, $code=1, \Exception $e=NULL) {
	$CRLF = "\n";
	if (empty($msg)) {
		$msg = System::isShell()
			? '<NULL>'
			: '&lt;NULL&gt;';
	} else
	if (!\is_string($msg)) {
		$msg = \print_r($msg, TRUE);
	}
	if (System::isShell()) {
		echo "\n *** FATAL: $msg *** \n\n";
	} else {
		echo '<pre style="color: black; background-color: #ffaaaa; '.
				'padding: 10px;"><font size="+2">FATAL: '.$msg.'</font></pre>'.$CRLF;
	}
	if ($code instanceof \Exception) {
		$e = $code;
		$code = 1;
	}
	if (debug()) {
		backtrace($e);
	}
	@\ob_flush();
	if ($code !== NULL) {
		ExitNow($code);
	}
}
function backtrace($e=NULL) {
	$isShell = System::isShell();
	$CRLF = "\n";
	$TAB  = "\t";
	if ($e == NULL) {
		$trace = \debug_backtrace();
	} else {
		$trace = $e->getTrace();
	}

	// ignored trace entries
	$ignore = [
		'init.php' => [
			'pxn\\phpUtils\\fail',
			'pxn\\phpUtils\\backtrace',
			'autoload',
			'__autoload',
		],
		'Globals.php' => [
			'pxn\\phpUtils\\fail',
			'pxn\\phpUtils\\backtrace',
		]
	];
	foreach ($trace as $index => $tr) {
		if (!isset($tr['file'])) {
			continue;
		}
		$file = $tr['file'];
		$func = $tr['function'];
		foreach ($ignore as $ignoreFile => $ignoreEntry) {
			if (Strings::EndsWith($file, $ignoreFile)) {
				if (\in_array($func, $ignoreEntry)) {
					unset ($trace[$index]);
					break;
				}
			}
		}
	}
	$trace = \array_values($trace);
	$trace = \array_reverse($trace, TRUE);
	if (!$isShell) {
		echo '<table style="background-color: #ffeedd; padding: 10px; '.
			'border-width: 1px; border-style: solid; border-color: #aaaaaa;">'.$CRLF;
	}

	// display trace
	$first   = TRUE;
	$evenodd = FALSE;
	foreach ($trace as $num => $tr) {
		$index = ((int) $num) + 1;
		if (!$first) {
			if ($isShell) {
				echo ' ----- ----- ----- ----- '.$CRLF;
			} else {
				echo '<tr><td height="20">&nbsp;</td></tr>'.$CRLF;
			}
		}
		$first = FALSE;
		$trArgs = '';
		foreach ($tr['args'] as $arg) {
			if (!empty($trArgs)) {
				$trArgs .= ', ';
			}
			if (!\is_string($arg)) {
				$trArgs .= \print_r($arg, TRUE);
			} else
			if (!$isShell && \mb_strpos($arg, $CRLF)) {
				$trArgs .= "<pre>{$arg}</pre>";
			} else {
				$trArgs .= $arg;
			}
		}
		$trFile = (isset($tr['file'])     ? $tr['file']     : '');
		$trClas = (isset($tr['class'])    ? $tr['class']    : '');
		$trFunc = (isset($tr['function']) ? $tr['function'] : '');
		$trLine = (isset($tr['line'])     ? $tr['line']     : '');
		$trBase = \basename($trFile);
		$trContainer = (empty($trClas) ? $trBase : $trClas);
		if ($isShell) {
			if (!empty($trLine)) {
				$trLine = Strings::PadLeft($trLine, 2, ' ');
				$trLine = "Line: $trLine - ";
			}
			echo "#{$index} - {$trLine}{$trFile}\n";
			echo " CALL: $trContainer -> {$trFunc}()\n";
			if (!empty($trArgs)) {
				echo " ARGS: $trArgs\n";
			}
		} else {
			$evenodd = ! $evenodd;
			$bgcolor = ($evenodd ? '#ffe0d0' : '#fff8e8');
			echo '<tr style="background-color: '.$bgcolor.';">'.$CRLF;
			echo $TAB.'<td><font size="-2">#'.$index.')</font></td>'.$CRLF;
			echo "$TAB<td>$trFile</td>$CRLF";
			echo "</tr>$CRLF";
			echo '<tr style="background-color: '.$bgcolor.';">'.$CRLF;
			echo "$TAB<td></td>$CRLF";
			echo "$TAB<td>".
				"<i>{$trContainer}</i> ".
				'<font size="-1">-&gt;</font> '.
				"<b>{$trFunc}()</b> ".
				" ( {$trArgs} ) ".
				(empty($trLine) ? '' : '<font size="-1">line: '.$trLine.'</font>' ).
				"</td>$CRLF";
			echo "</tr>$CRLF";
		}
	}
	if (!$isShell) {
		echo "</table>$CRLF";
	}
	echo $CRLF;
	@\ob_flush();
}



########################
##                    ##
##  init 3 - defines  ##
##                    ##
########################



// defines
\pxn\phpUtils\Defines::init();
if (\class_exists('\\pxn\\phpPortal\\DefinesPortal')) {
	\pxn\phpPortal\DefinesPortal::init();
}

// paths
\pxn\phpUtils\Paths::init();



########################
##                    ##
##  init 4 - configs  ##
##                    ##
########################



// configs
\pxn\phpUtils\ConfigGeneral::init();
if (\class_exists('\\pxn\\phpPortal\\ConfigPortal')) {
	\pxn\phpPortal\ConfigPortal::init();
}

// load shell args
if (System::isShell()) {
	ShellTools::init();
}



######################
##                  ##
##  init 5 - debug  ##
##                  ##
######################



// debug mode
global $pxnUtils_DEBUG;
$pxnUtils_DEBUG = NULL;
\pxn\phpUtils\ConfigGeneral::setDebugRef($pxnUtils_DEBUG);
$cfg = \pxn\phpUtils\Config::get(\pxn\phpUtils\Defines::KEY_CONFIG_GROUP_GENERAL);
$dao = $cfg->getDAO(\pxn\phpUtils\Defines::KEY_CFG_DEBUG);
$dao->setValidHandler('bool');

function debug($debug=NULL, $msg=NULL) {
	if ($debug !== NULL) {
		\pxn\phpUtils\ConfigGeneral::setDebug($debug, $msg);
	}
	return \pxn\phpUtils\ConfigGeneral::isDebug();
}

// by define
{
	if (\defined('\DEBUG')) {
		debug(\DEBUG, 'by define');
	}
	if (\defined('pxn\\phpUtils\\DEBUG')) {
		debug(\pxn\phpUtils\DEBUG, 'by namespaced define');
	}
}
// by file
{
	$entry  = Paths::entry();
	$base   = Paths::base();
	$common = Strings::CommonPath(
		$entry,
		$base
	);
	$paths = [
		$entry,
		$base,
		$common,
		$common."/.."
	];
	foreach ($paths as $path) {
		if (\file_exists($path."/debug")) {
			debug(TRUE, 'by file');
			break;
		}
		if (\file_exists($path."/DEBUG")) {
			debug(TRUE, 'by file');
			break;
		}
		if (\file_exists($path."/.debug")) {
			debug(TRUE, 'by file');
			break;
		}
	}
}
// by url
{
	$val = General::getVar('debug', 'bool');
	if ($val !== NULL) {
		// set cookie
		\setcookie(
			Defines::DEBUG_COOKIE,
			($val === TRUE ? '1' : '0'),
			0
		);
		debug($val, 'set cookie');
	} else {
		// from cookie
		$val = General::getVar(
			Defines::DEBUG_COOKIE,
			'bool',
			'cookie'
		);
		if ($val === TRUE) {
			debug($val, 'by cookie');
		}
	}
}
unset($val);
// ensure inited (default to false)
if ($pxnUtils_DEBUG === NULL) {
	debug(FALSE, 'default');
}



/*
// Kint backtracer
if (file_exists(paths::getLocal('portal').DIR_SEP.'kint.php')) {
	include(paths::getLocal('portal').DIR_SEP.'kint.php');
}
// php_error
if (file_exists(paths::getLocal('portal').DIR_SEP.'php_error.php')) {
	include(paths::getLocal('portal').DIR_SEP.'php_error.php');
}
// Kint backtracer
$kintPath = paths::getLocal('portal').DIR_SEP.'debug'.DIR_SEP.'kint'.DIR_SEP.'Kint.class.php';
if (file_exists($kintPath)) {
	//global $GLOBALS;
	//if (!@is_array(@$GLOBALS)) $GLOBALS = array();
	//$_kintSettings = &$GLOBALS['_kint_settings'];
	//$_kintSettings['traceCleanupCallback'] = function($traceStep) {
	//echo '<pre>';print_r($traceStep);exit();
	//	if (isset($traceStep['class']) && $traceStep['class'] === 'Kint')
	//		return NULL;
	//	if (isset($traceStep['function']) && \mb_strtolower($traceStep['function']) === '__tostring')
	//		$traceStep['function'] = '[object converted to string]';
	//	return $traceStep;
	//};
	//echo '<pre>';print_r($_kintSettings);exit();
	include($kintPath);
	}
	// php_error
	$phpErrorPath = paths::getLocal('portal').DIR_SEP.'debug'.DIR_SEP.'php_error.php';
	if (file_exists($phpErrorPath))
		include($phpErrorPath);
		if (function_exists('php_error\\reportErrors')) {
			$reportErrors = '\\php_error\\reportErrors';
			$reportErrors([
					'catch_ajax_errors'      => TRUE,
					'catch_supressed_errors' => FALSE,
					'catch_class_not_found'  => FALSE,
					'snippet_num_lines'      => 11,
					'application_root'       => __DIR__,
					'background_text'        => 'PSM',
			]);
		}
	}
}
// error page
public static function Error($msg) {
	echo '<div style="background-color: #ffbbbb;">'.CRLF;
	if (!empty($msg))
		echo '<h4>Error: '.$msg.'</h4>'.CRLF;
	echo '<h3>Backtrace:</h3>'.CRLF;
//	if (\method_exists('Kint', 'trace'))
//		\Kint::trace();
//	else
		echo '<pre>'.print_r(\debug_backtrace(), TRUE).'</pre>';
	echo '</div>'.CRLF;
//	\psm\Portal::Unload();
	exit(1);
}
*/

/*
\set_error_handler(
function ($severity, $msg, $filename, $line, array $err_context) {
	if (0 === error_reporting())
		return FALSE;
	switch($severity) {
	case E_ERROR:             throw new ErrorException            ($msg, 0, $severity, $filename, $line);
	case E_WARNING:           throw new WarningException          ($msg, 0, $severity, $filename, $line);
	case E_PARSE:             throw new ParseException            ($msg, 0, $severity, $filename, $line);
	case E_NOTICE:            throw new NoticeException           ($msg, 0, $severity, $filename, $line);
	case E_CORE_ERROR:        throw new CoreErrorException        ($msg, 0, $severity, $filename, $line);
	case E_CORE_WARNING:      throw new CoreWarningException      ($msg, 0, $severity, $filename, $line);
	case E_COMPILE_ERROR:     throw new CompileErrorException     ($msg, 0, $severity, $filename, $line);
	case E_COMPILE_WARNING:   throw new CoreWarningException      ($msg, 0, $severity, $filename, $line);
	case E_USER_ERROR:        throw new UserErrorException        ($msg, 0, $severity, $filename, $line);
	case E_USER_WARNING:      throw new UserWarningException      ($msg, 0, $severity, $filename, $line);
	case E_USER_NOTICE:       throw new UserNoticeException       ($msg, 0, $severity, $filename, $line);
	case E_STRICT:            throw new StrictException           ($msg, 0, $severity, $filename, $line);
	case E_RECOVERABLE_ERROR: throw new RecoverableErrorException ($msg, 0, $severity, $filename, $line);
	case E_DEPRECATED:        throw new DeprecatedException       ($msg, 0, $severity, $filename, $line);
	case E_USER_DEPRECATED:   throw new UserDeprecatedException   ($msg, 0, $severity, $filename, $line);
	}
});
class WarningException          extends \ErrorException {}
class ParseException            extends \ErrorException {}
class NoticeException           extends \ErrorException {}
class CoreErrorException        extends \ErrorException {}
class CoreWarningException      extends \ErrorException {}
class CompileErrorException     extends \ErrorException {}
class CompileWarningException   extends \ErrorException {}
class UserErrorException        extends \ErrorException {}
class UserWarningException      extends \ErrorException {}
class UserNoticeException       extends \ErrorException {}
class StrictException           extends \ErrorException {}
class RecoverableErrorException extends \ErrorException {}
class DeprecatedException       extends \ErrorException {}
class UserDeprecatedException   extends \ErrorException {}
*/

/*
\set_exception_handler(
function (\Exception $e) {
	echo '<h1>Uncaught Exception</h1>'.CRLF;
	echo '<h2>'.$e->getMessage().'</h2>'.CRLF;
	echo '<h3>Line '.$e->getLine().' of '.$e->getFile().'</h3>'.CRLF;
	foreach ($e->getTrace() as $t)
		\var_dump($t);
	exit(1);
});
*/



########################
##                    ##
##  init 6 - globals  ##
##                    ##
########################



require ('Globals.php');



return TRUE;
