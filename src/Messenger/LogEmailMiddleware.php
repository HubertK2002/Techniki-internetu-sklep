<?php

namespace App\Messenger;

use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Messenger\SendEmailMessage;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

final class LogEmailMiddleware implements MiddlewareInterface
{
	public function __construct(private LoggerInterface $logger) {}

	public function handle(Envelope $envelope, StackInterface $stack): Envelope
	{
		$msg = $envelope->getMessage();

		if ($msg instanceof SendEmailMessage) {
			$email = $msg->getMessage();

			$from = method_exists($email, 'getFrom')
				? array_map(fn($a) => $a->toString(), $email->getFrom())
				: [];
			$to = method_exists($email, 'getTo')
				? array_map(fn($a) => $a->toString(), $email->getTo())
				: [];
			$subject = method_exists($email, 'getSubject')
				? $email->getSubject()
				: null;

			$this->logger->info('Messenger email params', [
				'message_class' => $email::class,
				'from' => $from,
				'to' => $to,
				'subject' => $subject,
			]);
		}

		return $stack->next()->handle($envelope, $stack);
	}
}
