<?php
namespace core;

class DNS
{
	/** Strip domain from email */
	public static function mail_domain($email)
	{
		$idx = mb_strpos($email, "@");
		if ($idx === false) {
			user_error("DNS::mail_domain Not email=$email");
		}
		return mb_substr($email, 1+$idx);
	}

        /**
         * Create array of all parent domains.
         * example: $domain="kells.anonaddy.com" converts into $domains=["kells.anonaddy.com", "anonaddy.com"]
         * @return false|array false-value on domains containing more than 10 dots
         */
        public static function domains_all($domain) {
                $domains = [$domain];

                $last = $domain;
                $i = 0;
                for (; $i < 10; $i++) {
                        $pos = mb_strpos($last, ".");
                        if ($pos === false) break;

                        $last = mb_substr($last, $pos+1);
                        $domains[] = $last;
                }
                if ($i === 9) {
                        return false;
                }

                // Strip off TLD
                return array_slice($domains, 0, count($domains)-1);
        }
	
	/**
	 * Get MX-records for given domain - with A-record fallback (recursive func)
	 * TODO: Delete?
	 */
	public static function ip($domain, $dns=[], $protect=0)
	{
		if ($protect >= 5) {
			user_error(
				"Recursion bug, preventing infinite loop for domain="
				. $domain
			);
		}
		$dns[] = $domain;

		$fallback = [];
		$ips = [];
		$records = dns_get_record($domain, DNS_ALL);
		if ($records === false) {
			user_error("dns_get_record: fail for $domain");
		}
		foreach ($records as $record) {
			$type = strtoupper($record["type"]);
			if ($type === "MX" && $record["target"] !== $domain && !in_array($record["target"], $dns)) {
				$sub = self::ip($record["target"], $dns, $protect+1);
				if ($sub !== false) {
					$ips = array_merge($ips, $sub);
				}
			}
			if ($type === "A") {
				$fallback[] = $record["ip"];
			}
		}
		if (count($ips) === 0 && count($fallback) > 0) {
			return $fallback;
		}
		return count($ips) === 0 ? false : $ips;
	}
	
	// Ensure MX-record for email domain exists
	public static function mx($domain)
	{
		if (! checkdnsrr($domain, 'MX')) {
			return false;
		}
		return true;
	}

	public static function unique_ip($domain)
	{
		return array_unique(self::ip($domain));
	}
}
