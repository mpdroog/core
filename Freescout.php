<?php
namespace core;

class Freescout {
	private ?array $config = null;

	public function __construct(array $config)
	{
		$this->config = $config;
	}

	// https://api-docs.freescout.net/#create-conversation
	public function create_conversation(string $subject, string $body, string $email, ?string $phone = null): bool {
		$ch = curl_init();
		if ($ch === false) {
		    user_error("curl_init fail");
		}
		$opt = 1;
		$opt &= curl_setopt($ch, CURLOPT_URL, $this->config["base"] . "/api/conversations");
		$opt &= curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$opt &= curl_setopt($ch, CURLOPT_TIMEOUT, 3);
		$opt &= curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
		$opt &= curl_setopt($ch, CURLOPT_HTTPHEADER, [
		    "X-FreeScout-API-Key: " . $this->config["apikey"],
		    "Content-Type: application/json",
		    "Accept: application/json",
		]);
		$opt &= curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
		    "type" => "email",
		    "mailboxId" => $this->config["mboxid"],
		    "subject" => $subject,
		    "customer" => [
			"email" => $email,
			"phone" => $phone,
			"firstName" => "customer",
		    ],
		    "threads" => [
			[
			    "text" => $body,
			    "type" => "customer",
			    "customer" => [
				"email" => $email,
			    ]
			]
		    ],
		]));
		if ($opt !== 1) {
		    user_error("curl_setopt fail");
		}

		$res = curl_exec($ch);
		if ($res === false) {
		    user_error("curl_exec fail e=" . curl_error($ch));
		}
		$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		if ($code !== 201) {
		    error_log("freescout(http=$code) body=" . $res);
		}
		return $code === 201;
	}
}
