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

			$this->logger->info('Messenger email peek', [
				'from' => array_map(fn($a) => $a->toString(), $email->getFrom()),
				'to' => array_map(fn($a) => $a->toString(), $email->getTo()),
				'subject' => $email->getSubject(),
			]);
		}

		return $stack->next()->handle($envelope, $stack);
	}
}
