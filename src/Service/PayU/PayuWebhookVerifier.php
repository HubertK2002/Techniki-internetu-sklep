<?php

namespace App\Service\PayU;

use Symfony\Component\HttpFoundation\Request;

final class PayuWebhookVerifier
{
	public function __construct(private readonly PayuConfig $cfg) {}

	public function verify(Request $request, string $rawBody): bool
	{
		$header = $request->headers->get('OpenPayu-Signature')
			?? $request->headers->get('X-OpenPayU-Signature');

		if (!$header) {
			return false;
		}

		$parts = $this->parseHeader($header);
		$incoming = $parts['signature'] ?? null;
		$algo = strtolower((string)($parts['algorithm'] ?? 'md5'));

		if (!$incoming || $algo !== 'md5') {
			return false;
		}

		// wg docs: MD5(JSON_body + second_key) :contentReference[oaicite:8]{index=8}
		$expected = md5($rawBody.$this->cfg->secondKey);

		return hash_equals($expected, $incoming);
	}

	private function parseHeader(string $header): array
	{
		$out = [];
		foreach (explode(';', $header) as $chunk) {
			$chunk = trim($chunk);
			if ($chunk === '') continue;
			$kv = explode('=', $chunk, 2);
			if (count($kv) === 2) {
				$out[trim($kv[0])] = trim($kv[1]);
			}
		}
		return $out;
	}
}
