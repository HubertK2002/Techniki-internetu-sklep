<?php

namespace App\Controller;

use App\Entity\Order;
use App\Service\PayU\PayuPaymentService;
use App\Service\PayU\PayuPayloadFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PayuController extends AbstractController
{
	#[Route('/order/{id}/payu/start', name: 'payu_start', methods: ['POST'])]
	public function start(Order $order, PayuPaymentService $payu, PayuPayloadFactory $factory): Response
	{
		// access: tylko właściciel i tylko gdy editable/płatne itd.
		$this->denyAccessUnlessGranted('ROLE_USER');
		if ($order->getUser() !== $this->getUser()) {
			throw $this->createAccessDeniedException();
		}

		try {
			$payload = $factory->createForOrder($order);
			$redirect = $payu->startOrContinue($order, $payload);
			return $this->redirect($redirect);
		} catch (\Throwable $e) {
			$this->addFlash('error', $e->getMessage());
			return $this->redirectToRoute('order_show', ['id' => $order->getId()]);
		}
	}

	#[Route('/order/{id}/payu/refresh', name: 'payu_refresh', methods: ['POST'])]
	public function refresh(Order $order, PayuPaymentService $payu): Response
	{
		$this->denyAccessUnlessGranted('ROLE_USER');
		if ($order->getUser() !== $this->getUser()) {
			throw $this->createAccessDeniedException();
		}

		$payu->refreshStatus($order);

		return $this->redirectToRoute('order_show', ['id' => $order->getId()]);
	}

	#[Route('/order/{id}/payu/restart', name: 'payu_restart', methods: ['POST'])]
	public function restart(Order $order, PayuPaymentService $payu, PayuPayloadFactory $factory): Response
	{
		$this->denyAccessUnlessGranted('ROLE_USER');
		if ($order->getUser() !== $this->getUser()) {
			throw $this->createAccessDeniedException();
		}

		try {
			$payload = $factory->createForOrder($order);
			$redirect = $payu->restartPayment($order, $payload);
			return $this->redirect($redirect);
		} catch (\Throwable $e) {
			$this->addFlash('error', $e->getMessage());
			return $this->redirectToRoute('order_show', ['id' => $order->getId()]);
		}
	}
}
