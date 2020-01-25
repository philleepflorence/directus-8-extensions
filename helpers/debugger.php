<?php

/*
	Author - PhilleepFlorence
	Description - Developer Debugger Helper Functions
*/

namespace Directus\Custom\Helpers;
	
use Directus\Util\ArrayUtils;

class Debug 
{	
	/*
		Development Tool - Returns the caller of a method
	*/
	
	public static function Caller ()
	{
		$callers = debug_backtrace();
		
		$function = ArrayUtils::get($callers, '2.function');
		$class = ArrayUtils::get($callers, '2.class');
		$type = ArrayUtils::get($callers, '2.type');

		return ( $class ? $class . $type . $function : $function ) . '()';
	}
	
	/*
		Development Tool - Debug Multiple Arguments in JSON Format
		Print readable JSON data to screen and then QUIT
	*/
	
	public static function Json ()
	{
		$args = func_get_args();

		die(json_encode($args));
	}
	
	/*
		Development Tool - Debug Multiple Arguments in the Log
	*/
	
	public static function Log ()
	{
		$args = func_get_args();
		$log = [];
		$caller = SELF::caller();

		foreach ($args as $arg)
			$log[] = stripcslashes(( json_encode($arg) ));

		error_log($caller . ' - ' . implode(" . ", $log));
	}
	
	/*
		Development Tool - Debug Multiple Arguments in Readable Format
		Print readable pre formatted data to screen and then QUIT
	*/
	
	public static function Render ()
	{
		$args = func_get_args();

		reset($args);

		print '<pre>';

		print "\n";

		print sprintf('DEBUGGER: %s', Self::Caller());

		print "\n";

		foreach ($args as $arg):

			print "\n";

			print_r(gettype($arg) === 'boolean' ? json_encode($arg) : $arg);

			print "\n";

		endforeach;

		print "- \n";

		print '</pre>';

		exit();
	}
}