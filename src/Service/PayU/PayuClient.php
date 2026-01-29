<?php

namespace App\Service\PayU;

use Symfony\Contracts\HttpClient\HttpClientInterface;

final class PayuClient
{
	public function __construct(
		private readonly HttpClientInterface $http,
		private readonly PayuTokenProvider $tokens,
		private readonly PayuConfig $cfg,
	) {}

	public function createOrder(array $payload): array
	{
		$res = $this->requestCreateOrder($payload);

		$headers = $res->getHeaders(false);
		$body = $res->getContent(false);
		$status = $res->getStatusCode();

		if ($redirectUri = $this->extractLocation($headers)) {
			return [
				'redirectUri' => $redirectUri,
				'orderId' => $this->extractOrderIdFromRedirect($redirectUri),
			];
		}

		if ($data = $this->tryParseJson($body)) {
			if (($data['status']['statusCode'] ?? null) === 'ERROR_ORDER_NOT_UNIQUE') {
				return [
					'error' => 'ORDER_NOT_UNIQUE',
					'orderId' => $data['orderId'] ?? null,
					'extOrderId' => $data['extOrderId'] ?? null,
					'raw' => $data,
				];
			}

			// normalny success w JSON
			if (!empty($data['redirectUri'])) {
				return [
					'redirectUri' => $data['redirectUri'],
					'orderId' => $data['orderId'] ?? null,
					'status' => $data['status'] ?? null,
				];
			}
		}

		throw new \RuntimeException(sprintf(
			'PayU createOrder failed: HTTP %d body=%s',
			$status,
			substr($body, 0, 400)
		));
	}

	private function requestCreateOrder(array $payload)
	{
		$url = $this->cfg->apiBase().'/api/v2_1/orders';

		return $this->http->request('POST', $url, [
			'max_redirects' => 0,
			'headers' => [
				'Accept' => 'application/json',
				'Content-Type' => 'application/json',
				'Authorization' => 'Bearer '.$this->tokens->getAccessToken(),
			],
			'json' => $payload,
		]);
	}

	private function extractLocation(array $headers): ?string
	{
		if (!empty($headers['location'][0])) {
			return $headers['location'][0];
		}
		if (!empty($headers['Location'][0])) {
			return $headers['Location'][0];
		}
		return null;
	}

	private function tryParseJson(string $body): ?array
	{
		$body = trim($body);
		if ($body === '' || $body[0] !== '{') {
			return null;
		}

		$data = json_decode($body, true);
		return is_array($data) ? $data : null;
	}

	private function extractOrderIdFromRedirect(string $redirectUri): ?string
	{
		$parts = parse_url($redirectUri);
		if (!isset($parts['query'])) {
			return null;
		}
		parse_str($parts['query'], $qs);
		return isset($qs['orderId']) && is_string($qs['orderId']) ? $qs['orderId'] : null;
	}

	public function getOrder(string $payuOrderId): array
	{
		$url = $this->cfg->apiBase().'/api/v2_1/orders/'.$payuOrderId;

		$res = $this->http->request('GET', $url, [
			'headers' => [
				'Content-Type' => 'application/json',
				'Authorization' => 'Bearer '.$this->tokens->getAccessToken(),
			],
		]);

		$status = $res->getStatusCode();
		$data = $res->toArray(false);

		if ($status < 200 || $status >= 300) {
			throw new \RuntimeException('PayU getOrder error: HTTP '.$status.' body='.json_encode($data));
		}

		return $data;
	}
}
