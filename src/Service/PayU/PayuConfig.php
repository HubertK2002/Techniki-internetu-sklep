<?php

namespace App\Service\PayU;

final class PayuConfig
{
	public function __construct(
		public readonly string $env,
		public readonly string $posId,
		public readonly string $clientId,
		public readonly string $clientSecret,
		public readonly ?string $secondKey,
		public readonly ?string $notifyUrl,
		public readonly string $continueBase,
	) {
		if ($this->posId === '' || $this->clientId === '' || $this->clientSecret === '') {
			throw new \RuntimeException('PayU config missing: PAYU_POS_ID / PAYU_CLIENT_ID / PAYU_CLIENT_SECRET');
		}
		if (!in_array($this->env, ['sandbox', 'prod'], true)) {
			throw new \RuntimeException('PayU config: PAYU_ENV must be sandbox|prod');
		}
	}

	public function apiBase(): string
	{
		return $this->env === 'sandbox'
			? 'https://secure.snd.payu.com'
			: 'https://secure.payu.com';
	}

	public function oauthUrl(): string
	{
		return $this->apiBase().'/pl/standard/user/oauth/authorize';
	}
}
