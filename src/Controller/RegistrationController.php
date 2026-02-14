<?php
namespace App\Controller;

use App\Entity\User;
use App\Entity\Wishlist;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use App\Form\ResendVerificationEmailType;
use App\Security\UserAuthenticator;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class RegistrationController extends AbstractController
{
    public function __construct(private EmailVerifier $emailVerifier)
    {
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            $wishlist = new Wishlist();
            $wishlist->setUser($user);

            $entityManager->persist($user);
            $entityManager->persist($wishlist);
            $entityManager->flush();

            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('automat@test.pl', 'Hubert Kwiecień Bot'))
                    ->to((string) $user->getEmail())
                    ->subject('Please Confirm your Email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(
		Request $request,
		TranslatorInterface $translator,
		UserRepository $userRepository,
		UserAuthenticatorInterface $userAuthenticator,
		UserAuthenticator $authenticator
	): Response
    {
        $id = $request->query->get('id');

        if (null === $id) {
            return $this->redirectToRoute('app_register');
        }

        $user = $userRepository->find($id);

        if (null === $user) {
            return $this->redirectToRoute('app_register');
        }

        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('app_register');
        }

        $this->addFlash('success', 'Your email address has been verified.');

        return $userAuthenticator->authenticateUser($user, $authenticator, $request);
    }

	#[Route('/verify-email/resend', name: 'app_resend_verification_email')]
	public function resendVerificationEmail(
		Request $request,
		UserRepository $userRepository,
		\App\Security\EmailVerifier $emailVerifier,
	): Response {
		$form = $this->createForm(ResendVerificationEmailType::class);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$emailValue = trim(mb_strtolower((string) $form->get('email')->getData()));
			$user = $userRepository->findOneBy(['email' => $emailValue]);

			if ($user && !$user->isVerified()) {
				$email = (new TemplatedEmail())
					->from(new Address('automat@test.pl', 'Hubert Kwiecień Bot'))
					->to($user->getEmail())
					->subject('Please Confirm your Email')
					->htmlTemplate('registration/confirmation_email.html.twig');

				$emailVerifier->sendEmailConfirmation('app_verify_email', $user, $email);
			}

			$this->addFlash('success', 'If an account exists, we’ve sent you a verification email.');
			return $this->redirectToRoute('app_login');
		}

		return $this->render('registration/resend_verification_email.html.twig', [
			'form' => $form,
		]);
	}
}
