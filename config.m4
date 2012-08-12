dnl $Id$
dnl config.m4 for extension fwrapper

dnl Comments in this file start with the string 'dnl'.
dnl Remove where necessary. This file will not work
dnl without editing.

PHP_ARG_ENABLE(fwrapper, whether to enable fwrapper support,
[  --enable-fwrapper           Enable fwrapper support])

if test "$PHP_FWRAPPER" != "no"; then
  dnl Write more examples of tests here...

  dnl # --with-fwrapper -> check with-path
  dnl SEARCH_PATH="/usr/local /usr"     # you might want to change this
  dnl SEARCH_FOR="/include/fwrapper.h"  # you most likely want to change this
  dnl if test -r $PHP_FWRAPPER/$SEARCH_FOR; then # path given as parameter
  dnl   FWRAPPER_DIR=$PHP_FWRAPPER
  dnl else # search default path list
  dnl   AC_MSG_CHECKING([for fwrapper files in default path])
  dnl   for i in $SEARCH_PATH ; do
  dnl     if test -r $i/$SEARCH_FOR; then
  dnl       FWRAPPER_DIR=$i
  dnl       AC_MSG_RESULT(found in $i)
  dnl     fi
  dnl   done
  dnl fi
  dnl
  dnl if test -z "$FWRAPPER_DIR"; then
  dnl   AC_MSG_RESULT([not found])
  dnl   AC_MSG_ERROR([Please reinstall the fwrapper distribution])
  dnl fi

  dnl # --with-fwrapper -> add include path
  dnl PHP_ADD_INCLUDE($FWRAPPER_DIR/include)

  dnl # --with-fwrapper -> check for lib and symbol presence
  dnl LIBNAME=fwrapper # you may want to change this
  dnl LIBSYMBOL=fwrapper # you most likely want to change this 

  dnl PHP_CHECK_LIBRARY($LIBNAME,$LIBSYMBOL,
  dnl [
  dnl   PHP_ADD_LIBRARY_WITH_PATH($LIBNAME, $FWRAPPER_DIR/lib, FWRAPPER_SHARED_LIBADD)
  dnl   AC_DEFINE(HAVE_FWRAPPERLIB,1,[ ])
  dnl ],[
  dnl   AC_MSG_ERROR([wrong fwrapper lib version or lib not found])
  dnl ],[
  dnl   -L$FWRAPPER_DIR/lib -lm
  dnl ])
  dnl
  dnl PHP_SUBST(FWRAPPER_SHARED_LIBADD)

  PHP_NEW_EXTENSION(fwrapper, fwrapper.c, $ext_shared)
fi
