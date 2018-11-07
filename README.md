`core` is a loose webdev lib (all code is focussed on minimalism) to quickly write PHP-code without big frameworks or horribly complex libraries for basic stuff like SQL, input validation etc..

Use of this source code is governed by a BSD-style license that can be found in the LICENSE file.

Composer
===================
Add to your project
```json
{
    "require": {
    	"mpdroog/core": "master",
    },
    "minimum-stability": "dev",

	"repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/mpdroog/core"
        }
    ]
}
```

php.ini
===================
Compatible php.ini (if you want to bump security up big time)
```
[PHP]
; Security
; =========
; Limit allowed directory
open_basedir = /path/to/project

; Limit the allowed functions
; 1) No shell executes
; 2) No ini parsing/source show or rot13 (used by hackkits)
disable_functions = exec,passthru,shell_exec,system,proc_open,popen,curl_multi_exec,parse_ini_file,show_source,str_rot13,dl,ini_set,php_ini_loaded_file,php_ini_scanned_files,zend_version,gc_disable,phpinfo,phpversion,putenv,php_uname,php_logo_guid,get_current_user,get_loaded_extensions,phpversion,ord,mail

; Hide PHP-details
expose_php = Off

; No file uploads
file_uploads=Off

; Report ALL errors
error_reporting = E_ALL

; UTF-8 encoding
default_charset = "UTF-8"
; Block URL calls with fopen
allow_url_fopen = Off

[Date]
; Set timezone
date.timezone = UTC

[Session]
; Don't allow session_id overrides
session.use_strict_mode = 1
; Session-cookie name
session.name = sess
; JS cannot read session
session.cookie_httponly = On
; https only
session.cookie_secure = On
```
