<?php
namespace core;
use core\Error;
use core\Env;

class Auth {
        public static function ldap(array $cfg, array $loginPass)
        {
                if (strlen($loginPass[0]) === 0) return false;
                if (strlen($loginPass[1]) === 0) return false;

                $ldapconn = ldap_connect($cfg['host']);
                if ($ldapconn === false) {
                        user_error("ldap_connect failed");
                }
                // ldap_set_option(NULL, LDAP_OPT_DEBUG_LEVEL, 7);
                ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
                ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0);
                ldap_set_option($ldapconn, LDAP_OPT_NETWORK_TIMEOUT, 3);
                ldap_set_option($ldapconn, LDAP_OPT_TIMELIMIT, 3);

                Error::mute();
                $bind = ldap_bind($ldapconn, sprintf($cfg['basedn'], $loginPass[0]), $loginPass[1]);
                Error::unmute();
                ldap_close($ldapconn);
                return $bind;
        }

        public static function blocking(string $realm, array $cfg, $authFn)
        {
                $v = Env::protocol();
                list($requser, $reqpass) = Env::userPass();
                if ($requser === "" || $reqpass === "") {
                        header(sprintf('WWW-Authenticate: Basic realm="%s"', $realm));
                        header("HTTP/$v 401 Unauthorized");
                        echo "LDAP: Invalid user or pass\n";
                        exit;
                }
                if (! $authFn($cfg, [$requser, $reqpass])) {
                        header(sprintf('WWW-Authenticate: Basic realm="%s"', $realm));
                        header("HTTP/$v 403 Unauthorized");
                        echo "Invalid user/pass\n";
                        exit;
                }
        }
}
