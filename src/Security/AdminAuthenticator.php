<?php

namespace App\Security;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

final class AdminAuthenticator extends AbstractLoginFormAuthenticator
{
	use TargetPathTrait;

	public const LOGIN_ROUTE = 'admin_login';

	public function __construct(
		private readonly UrlGeneratorInterface $urlGenerator,
		private readonly UserRepository $userRepository,
	) {}

	public function authenticate(Request $request): Passport
	{
		$email = $request->getPayload()->getString('email');
		$request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $email);

		return new Passport(
			new UserBadge($email, function (string $userIdentifier) {
				$user = $this->userRepository->findOneBy(['email' => $userIdentifier]);

				if (!$user) {
					return null;
				}

				// wymuś weryfikację maila (jak w normalnym loginie)
				if (!$user->isVerified()) {
					throw new CustomUserMessageAccountStatusException(
						'Please verify your email before logging in.'
					);
				}

				// wymuś rolę admina
				if (!in_array('ROLE_ADMIN', $user->getRoles(), true)) {
					throw new CustomUserMessageAccountStatusException(
						'You do not have access to the admin panel.'
					);
				}

				return $user;
			}),
			new PasswordCredentials($request->getPayload()->getString('password')),
			[
				new CsrfTokenBadge('authenticate', $request->getPayload()->getString('_csrf_token')),
				new RememberMeBadge(),
			]
		);
	}

	public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
	{
		if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
			return new RedirectResponse($targetPath);
		}

		return new RedirectResponse($this->urlGenerator->generate('admin'));
	}

	protected function getLoginUrl(Request $request): string
	{
		return $this->urlGenerator->generate(self::LOGIN_ROUTE);
	}
}
