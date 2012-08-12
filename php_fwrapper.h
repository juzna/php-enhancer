/*
  +----------------------------------------------------------------------+
  | PHP Version 5                                                        |
  +----------------------------------------------------------------------+
  | Copyright (c) 1997-2012 The PHP Group                                |
  +----------------------------------------------------------------------+
  | This source file is subject to version 3.01 of the PHP license,      |
  | that is bundled with this package in the file LICENSE, and is        |
  | available through the world-wide-web at the following url:           |
  | http://www.php.net/license/3_01.txt                                  |
  | If you did not receive a copy of the PHP license and are unable to   |
  | obtain it through the world-wide-web, please send a note to          |
  | license@php.net so we can mail you a copy immediately.               |
  +----------------------------------------------------------------------+
  | Author:                                                              |
  +----------------------------------------------------------------------+
*/

/* $Id$ */

#ifndef PHP_FWRAPPER_H
#define PHP_FWRAPPER_H

extern zend_module_entry fwrapper_module_entry;
#define phpext_fwrapper_ptr &fwrapper_module_entry

#ifdef PHP_WIN32
#	define PHP_FWRAPPER_API __declspec(dllexport)
#elif defined(__GNUC__) && __GNUC__ >= 4
#	define PHP_FWRAPPER_API __attribute__ ((visibility("default")))
#else
#	define PHP_FWRAPPER_API
#endif

#ifdef ZTS
#include "TSRM.h"
#endif

PHP_MINIT_FUNCTION(fwrapper);
PHP_MSHUTDOWN_FUNCTION(fwrapper);
PHP_RINIT_FUNCTION(fwrapper);
PHP_RSHUTDOWN_FUNCTION(fwrapper);
PHP_MINFO_FUNCTION(fwrapper);

PHP_FUNCTION(fwrapper_register);

int fwrapper_stream_open(const char *filename, zend_file_handle *handle TSRMLS_DC);


ZEND_BEGIN_MODULE_GLOBALS(fwrapper)
	zend_bool registered;
	zend_fcall_info fci;
	zend_fcall_info_cache fci_cache;
ZEND_END_MODULE_GLOBALS(fwrapper)

/* In every utility function you add that needs to use variables 
   in php_fwrapper_globals, call TSRMLS_FETCH(); after declaring other 
   variables used by that function, or better yet, pass in TSRMLS_CC
   after the last function argument and declare your utility function
   with TSRMLS_DC after the last declared argument.  Always refer to
   the globals in your function as FWRAPPER_G(variable).  You are 
   encouraged to rename these macros something shorter, see
   examples in any other php module directory.
*/

#ifdef ZTS
#define FWRAPPER_G(v) TSRMG(fwrapper_globals_id, zend_fwrapper_globals *, v)
#else
#define FWRAPPER_G(v) (fwrapper_globals.v)
#endif

#endif	/* PHP_FWRAPPER_H */


/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: noet sw=4 ts=4 fdm=marker
 * vim<600: noet sw=4 ts=4
 */
