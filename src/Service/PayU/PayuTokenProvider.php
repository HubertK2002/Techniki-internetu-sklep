<?php

namespace App\Service\PayU;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class PayuTokenProvider
{
	public function __construct(
		private readonly HttpClientInterface $http,
		private readonly CacheInterface $cache,
		private readonly PayuConfig $cfg,
	) {}

	public function getAccessToken(): string
	{
		$key = 'payu_oauth_token_'.$this->cfg->env.'_'.$this->cfg->clientId;

		return $this->cache->get($key, function (ItemInterface $item) {
			$res = $this->http->request('POST', $this->cfg->oauthUrl(), [
				'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
				'body' => [
					'grant_type' => 'client_credentials',
					'client_id' => $this->cfg->clientId,
					'client_secret' => $this->cfg->clientSecret,
				],
			]);

			$data = $res->toArray(false);

			if (($data['access_token'] ?? '') === '') {
				throw new \RuntimeException('PayU OAuth: brak access_token.');
			}

			$expires = (int) ($data['expires_in'] ?? 0);
			// zostawiamy margines bezpieczeństwa
			$item->expiresAfter(max(60, $expires - 60));

			return (string) $data['access_token'];
		});
	}
}
