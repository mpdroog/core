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
disable_functions = exec,passthru,shell_exec,system,proc_open,popen,curl_multi_exec,parse_ini_file,show_source,str_rot13,dl,ini_set,php_ini_loaded_file,php_ini_scanned_files,zend_version,gc_disable,phpinfo,phpversion,putenv,php_uname,php_logo_guid,get_current_user,get_loaded_extensions,phpversion,ord,mail,highlight_file,ignore_user_abort,highlight_file

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
allow_url_include = Off

; Limit 30sec (slower script should be optimized/rewritten)
max_input_time = 30
max_execution_time = 30
; Limit 8MB
memory_limit = 8M

; Limit input flood
post_max_size = 256K
max_input_vars = 100

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

sql.abuselimit
```
CREATE TABLE `abuselimit` (
  `ratelimit_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ratelimit_ip` varchar(80) NOT NULL,
  `ratelimit_count` int(10) unsigned NOT NULL,
  `ratelimit_time_updated` int(10) unsigned NOT NULL,
  `ratelimit_time_added` int(10) unsigned NOT NULL,
  PRIMARY KEY (`ratelimit_id`),
  UNIQUE KEY `unique_ip` (`ratelimit_ip`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;
```
