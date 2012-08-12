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

#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#include "php.h"
#include "php_ini.h"
#include "ext/standard/info.h"
#include "php_fwrapper.h"

ZEND_DECLARE_MODULE_GLOBALS(fwrapper)


/* True global resources - no need for thread safety here */
static int le_fwrapper;

/* {{{ fwrapper_functions[]
 *
 * Every user visible function must have an entry in fwrapper_functions[].
 */
const zend_function_entry fwrapper_functions[] = {
	PHP_FE(fwrapper_register,	NULL)		/* For testing, remove later. */
	PHP_FE_END	/* Must be the last line in fwrapper_functions[] */
};
/* }}} */

/* {{{ fwrapper_module_entry
 */
zend_module_entry fwrapper_module_entry = {
#if ZEND_MODULE_API_NO >= 20010901
	STANDARD_MODULE_HEADER,
#endif
	"fwrapper",
	fwrapper_functions,
	PHP_MINIT(fwrapper),
	PHP_MSHUTDOWN(fwrapper),
	PHP_RINIT(fwrapper),		/* Replace with NULL if there's nothing to do at request start */
	PHP_RSHUTDOWN(fwrapper),	/* Replace with NULL if there's nothing to do at request end */
	PHP_MINFO(fwrapper),
#if ZEND_MODULE_API_NO >= 20010901
	"0.1", /* Replace with version number for your extension */
#endif
	STANDARD_MODULE_PROPERTIES
};
/* }}} */

#ifdef COMPILE_DL_FWRAPPER
ZEND_GET_MODULE(fwrapper)
#endif

/* {{{ PHP_INI
 */
/* Remove comments and fill if you need to have entries in php.ini
PHP_INI_BEGIN()
    STD_PHP_INI_ENTRY("fwrapper.global_value",      "42", PHP_INI_ALL, OnUpdateLong, global_value, zend_fwrapper_globals, fwrapper_globals)
    STD_PHP_INI_ENTRY("fwrapper.global_string", "foobar", PHP_INI_ALL, OnUpdateString, global_string, zend_fwrapper_globals, fwrapper_globals)
PHP_INI_END()
*/
/* }}} */

/* {{{ php_fwrapper_init_globals
 */
/* Uncomment this function if you have INI entries
static void php_fwrapper_init_globals(zend_fwrapper_globals *fwrapper_globals)
{
	fwrapper_globals->global_value = 0;
	fwrapper_globals->global_string = NULL;
}
*/
/* }}} */

/* {{{ PHP_MINIT_FUNCTION
 */
PHP_MINIT_FUNCTION(fwrapper)
{
	/* If you have INI entries, uncomment these lines 
	REGISTER_INI_ENTRIES();
	*/
	zend_stream_open_function = fwrapper_stream_open;


	return SUCCESS;
}
/* }}} */

/* {{{ PHP_MSHUTDOWN_FUNCTION
 */
PHP_MSHUTDOWN_FUNCTION(fwrapper)
{
	/* uncomment this line if you have INI entries
	UNREGISTER_INI_ENTRIES();
	*/
	return SUCCESS;
}
/* }}} */

/* Remove if there's nothing to do at request start */
/* {{{ PHP_RINIT_FUNCTION
 */
PHP_RINIT_FUNCTION(fwrapper)
{
	// init variables
	FWRAPPER_G(registered) = 0;
	FWRAPPER_G(fci) = empty_fcall_info;
	FWRAPPER_G(fci_cache) = empty_fcall_info_cache;

	return SUCCESS;
}
/* }}} */

/* Remove if there's nothing to do at request end */
/* {{{ PHP_RSHUTDOWN_FUNCTION
 */
PHP_RSHUTDOWN_FUNCTION(fwrapper)
{
	return SUCCESS;
}
/* }}} */

/* {{{ PHP_MINFO_FUNCTION
 */
PHP_MINFO_FUNCTION(fwrapper)
{
	php_info_print_table_start();
	php_info_print_table_header(2, "fwrapper support", "enabled");
	php_info_print_table_end();

	/* Remove comments if you have entries in php.ini
	DISPLAY_INI_ENTRIES();
	*/
}
/* }}} */


/* {{{ proto bool fwrapper_register(callable cb)
   Wrapper which gets called everytime Zend engine is opening a file */
PHP_FUNCTION(fwrapper_register)
{
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "f", &FWRAPPER_G(fci), &FWRAPPER_G(fci_cache)) == FAILURE) {
		return;
	}

	FWRAPPER_G(registered) = 1;

	RETURN_TRUE;
}
/* }}} */

int fwrapper_stream_open(const char *filename, zend_file_handle *handle TSRMLS_DC) /* {{{ */
{
//	printf("Opening %s\n", filename);
	if (strncmp(filename, "raw:", sizeof("raw:") - 1) == 0) { // raw: prefix -> normal
		return php_stream_open_for_zend_ex(filename + 4, handle, USE_PATH|REPORT_ERRORS|STREAM_OPEN_FOR_INCLUDE TSRMLS_CC);

	} else if(FWRAPPER_G(registered)) { // mit der wrapper
		zval *path, *retval_ptr = NULL;
		zval **args[1];

		// path as zval
		MAKE_STD_ZVAL(path);
		ZVAL_STRING(path, filename, 0);

		args[0] = &path;

		zend_fcall_info *fci = & FWRAPPER_G(fci);
		fci->param_count = 1;
		fci->params = args;
		fci->retval_ptr_ptr = &retval_ptr;
		fci->no_separation = 0;

		if (zend_call_function(fci, &FWRAPPER_G(fci_cache) TSRMLS_CC) == SUCCESS && retval_ptr) {
			if (Z_TYPE_P(retval_ptr) == IS_STRING) {
				filename = Z_STRVAL_P(retval_ptr);
			}

			efree(retval_ptr);
		}

		efree(path);

		return php_stream_open_for_zend_ex(filename, handle, USE_PATH|REPORT_ERRORS|STREAM_OPEN_FOR_INCLUDE TSRMLS_CC);

	} else { // default
		return php_stream_open_for_zend_ex(filename, handle, USE_PATH|REPORT_ERRORS|STREAM_OPEN_FOR_INCLUDE TSRMLS_CC);
	}
}
/* }}} */


/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: noet sw=4 ts=4 fdm=marker
 * vim<600: noet sw=4 ts=4
 */
