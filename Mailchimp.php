<?php
namespace core;

class Mailchimp
{
	const MIME_JSON = "application/json";
	const PAGINATION = 10;
	private $config = null;

	public function __construct(array $config)
	{
		$this->config = $config;
	}

	public function call($method, $path, array $j)
	{
		$allownull = isset($j["allownull"]);
		unset($j["allownull"]);

		$config = $this->config;
		$auth = base64_encode('user:' . $config["api_key"]);

		$ch = curl_init();
		if ($ch === false) {
			user_error("curl_init fail");
		}
		$url = sprintf("https://%s.api.mailchimp.com/3.0$path", $config["api_server"], $config["list_id"]);
		if ($method === "GET") {
			$url .= "?" . http_build_query($j);
		}

		$ok = 1;
		$ok &= curl_setopt($ch, CURLOPT_URL, $url);
		$ok &= curl_setopt($ch, CURLOPT_HTTPHEADER, [
			'Accept: application/json',
			'Content-Type: application/json',
			'Authorization: Basic ' . base64_encode('user:' . $config["api_key"])
		]);
		$ok &= curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
		$ok &= curl_setopt($ch, CURLOPT_USERAGENT, 'mpdroog/core');
		$ok &= curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$ok &= curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		$ok &= curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // TODO: dataleak?
		if ($method !== "GET") {
			$ok &= curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($j));
		}
		if ($ok !== 1) {
			user_error("curl_setopt fail");
		}

		$res = curl_exec($ch);
		if ($res === false) {
			user_error(curl_error($ch));
		}
		$http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
		$ct = "text/plain";
		if ($http !== 204 && substr($contentType, 0, strlen(self::MIME_JSON)) === self::MIME_JSON) {
			$res = json_decode($res, true);
			if (! is_array($res) && !$allownull) {
				error_log("WARN: json body but got non-JSON?");
			}
			$ct = "json";
		}

		curl_close($ch);
		return ["url" => $url, "http" => $http, "contentType" => $ct, "body" => $res];
	}

	public function batches($opts)
	{
		return $this->call("GET", "/batches", $opts);
	}
	public function batch($id, $opts)
	{
		return $this->call("GET", "/batches/$id", $opts);
	}
	public function batch_delete($id, $opts)
	{
		$opts["allownull"] = true;
		return $this->call("DELETE", "/batches/$id", $opts);
	}
	public function members($opts)
	{
		return $this->call("GET", "/lists/%s/members", $opts);
	}
	public function patch_member($email, $opts)
	{
		if (! isset($opts["merge_fields"])) {
			user_error("Mailchimp::patch_member misses merge_fields in opts-var");
		}
		if (! isset($opts["language"])) {
			user_error("Mailchimp::patch_member misses language in opts-var");
		}
		return $this->call(
			"PATCH",
			"/lists/%s/members/" . md5($member["email_address"]),
			$opts
		);
	}
	public function put_member($email, $opts) {
		if (! isset($opts["merge_fields"])) {
			user_error("Mailchimp::put_member misses merge_fields in opts-var");
		}
		if (! isset($opts["language"])) {
			user_error("Mailchimp::put_member misses language in opts-var");
		}
		return $this->call(
			"PUT",
			"/lists/%s/members/" . md5($email),
			$opts
		);
	}
	public function post_batch($opts)
	{
		return $this->call("POST", "/batches", $opts);
	}
	public function delete_member($email, $opts)
	{
		return $this->call(
			"DELETE",
			"/lists/%s/members/" . md5($email),
			$opts
		);
	}
}
