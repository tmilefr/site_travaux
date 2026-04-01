<?php 
defined('BASEPATH') || exit('No direct script access allowed');
/**
 * Errors_hook Hook Class
 *
 * This class contains functions that handles parse erros, fatal errors and exceptions
 *
 * @author		
 * @license		Apache License, Version 2.0 (http://www.opensource.org/licenses/apache2.0.php)
 * @link		
 * @package		OpenReceptor CMS
 * @version		Version 1.0
 */
class Errors_hook {

	/**
	 * Error Catcher
	 *
	 * Sets the user functions for parse errors, fatal errors and exceptions
	 *
	 * @access	public
	 * @return	void
	 */
	public function error_catcher()
	{

		set_error_handler(array($this, 'handle_errors'));

		set_exception_handler(array($this, 'handle_exceptions'));

		register_shutdown_function(array($this, 'handle_fatal_errors'));

	}

	/**
	 * Fatal Error Handler
	 *
	 * Accesses output buffers on shutdown, formats the error message and redirects
	 *
	 * @access	public
	 * @return	void
	 */
	public function handle_fatal_errors()
	{

		if (($error = error_get_last())) {

			$buffer = ob_get_contents();

			ob_clean();
			
			$msg = new StdClass();
			$msg->from = 'handle_fatal_errors';
			$msg->type = 'Error Type: ['.$error['type'].'] '.$this->_friendly_error_type($error['type']);
			$msg->message = $error['message'];
			$msg->file = $error['file'];
			$msg->line = $error['line'];
			$msg->backtrace =  $buffer ;

			$this->_save_error($msg);

		}

	}

	/**
	 * Exception Handler
	 *
	 * Accesses exception class on shutdown, formats the error message and redirects
	 *
	 * @access	public
	 * @return	void
	 */
	public function handle_exceptions($exception)
	{
		$msg = new StdClass();
		$msg->from = 'handle_exceptions';
		$msg->type = get_class($exception);
		$msg->message = $exception->getMessage();
		$msg->file = $exception->getFile();
		$msg->line = $exception->getLine();
		$msg->backtrace = $exception->getTraceAsString();

		$this->_save_error($msg);
	}

	/**
	 * Parse Error Handler
	 *
	 * Accesses parse errors, formats the error message and redirects
	 *
	 * @access	public
	 * @param 	int
	 * @param 	string
	 * @param 	string
	 * @param 	int
	 * @return 	void
	 */
	public function handle_errors($errno, $errstr, $errfile, $errline)
	{

		if (!(error_reporting() & $errno))
		{

			return;

		}

		$msg = new StdClass();
		$msg->from = 'handle_errors';
		$msg->type = 'Error Type: ['.$errno.'] '.$this->_friendly_error_type($errno).'';
		$msg->message = $errstr;
		$msg->file = $errfile;
		$msg->line = $errline;
		$msg->backtrace = "";

		$this->_save_error($msg);
	}

	/**
	 * Redirection
	 *
	 * Stores the error message in session and redirects to inhibitor hanlder
	 *
	 * @access	private
	 * @param	string
	 * @return	void
	 */
	private function _save_error($message)
	{

		$CI =& get_instance();
		$CI->load->library('session');
		$CI->_debug($message);
	}

	/**
	 * Error Type Helper
	 *
	 * Translates error codes to something more human
	 *
	 * @access	private
	 * @param	string
	 * @return	string
	 */
	private function _friendly_error_type($type)
	{

		switch($type)
		{
			case E_ERROR: // 1
				return 'Fatal error';
			case E_WARNING: // 2
				return 'Warning';
			case E_PARSE: // 4
				return 'Parse error';
			case E_NOTICE: // 8
				return 'Notice';
			case E_CORE_ERROR: // 16
				return 'Core fatal error';
			case E_CORE_WARNING: // 32
				return 'Core warning';
			case E_COMPILE_ERROR: // 64
				return 'Compile-time fatal error';
			case E_COMPILE_WARNING: // 128
				return 'Compile-time warning';
			case E_USER_ERROR: // 256
				return 'Fatal user-generated error';
			case E_USER_WARNING: // 512
				return 'User-generated warning';
			case E_USER_NOTICE: // 1024
				return 'User-generated notice';
			case E_STRICT: // 2048
				return 'E_STRICT';
			case E_RECOVERABLE_ERROR: // 4096
				return 'Catchable fatal error';
			case E_DEPRECATED: // 8192
				return 'E_DEPRECATED';
			case E_USER_DEPRECATED: // 16384
				return 'E_USER_DEPRECATED';
		}

		return $type;

	}


}

/* End of file Errors_hook.php */
/* Location: ./application/hooks/Errors_hook.php */
