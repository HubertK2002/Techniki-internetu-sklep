<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\User;
use App\Repository\WishlistRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class WishlistController extends AbstractController
{
	#[IsGranted('ROLE_USER')]
	#[Route('/wishlist', name: 'wishlist_show', methods: ['GET'])]
	public function show(WishlistRepository $wishlistRepository): Response
	{
		$user = $this->getUser();
		if (!$user instanceof User) {
			throw $this->createAccessDeniedException();
		}

		$wishlist = $wishlistRepository->findOneByUser($user);
		if (!$wishlist) {
			throw $this->createNotFoundException('Wishlist nie istnieje dla tego konta.');
		}

		return $this->render('wishlist/show.html.twig', [
			'wishlist' => $wishlist,
		]);
	}

	#[IsGranted('ROLE_USER')]
	#[Route('/wishlist/add/{id}', name: 'wishlist_add', requirements: ['id' => '\\d+'], methods: ['POST'])]
	public function add(
		Product $product,
		Request $request,
		WishlistRepository $wishlistRepository,
		EntityManagerInterface $em
	): Response {
		$user = $this->getUser();
		if (!$user instanceof User) {
			throw $this->createAccessDeniedException();
		}

		if (!$this->isCsrfTokenValid('wishlist_add_'.$product->getId(), (string) $request->request->get('_token'))) {
			if ($request->isXmlHttpRequest()) {
				return new JsonResponse(['ok' => false, 'error' => 'invalid_csrf'], 400);
			}
			$this->addFlash('error', 'Nieprawidlowy token formularza.');
			return $this->redirectToRoute('product_index');
		}

		$wishlist = $wishlistRepository->findOneByUser($user);
		if (!$wishlist) {
			if ($request->isXmlHttpRequest()) {
				return new JsonResponse(['ok' => false, 'error' => 'wishlist_not_found'], 404);
			}
			throw $this->createNotFoundException('Wishlist nie istnieje dla tego konta.');
		}

		$wishlist->addProduct($product);
		$em->flush();

		if ($request->isXmlHttpRequest()) {
			return new JsonResponse([
				'ok' => true,
				'action' => 'add',
				'productId' => $product->getId(),
				'toggleHtml' => $this->renderView('wishlist/_toggle_button.html.twig', [
					'product' => $product,
					'in_wishlist' => true,
				]),
			]);
		}

		return $this->redirect($request->headers->get('referer') ?: $this->generateUrl('product_index'));
	}

	#[IsGranted('ROLE_USER')]
	#[Route('/wishlist/remove/{id}', name: 'wishlist_remove', requirements: ['id' => '\\d+'], methods: ['POST'])]
	public function remove(
		Product $product,
		Request $request,
		WishlistRepository $wishlistRepository,
		EntityManagerInterface $em
	): Response {
		$user = $this->getUser();
		if (!$user instanceof User) {
			throw $this->createAccessDeniedException();
		}

		if (!$this->isCsrfTokenValid('wishlist_remove_'.$product->getId(), (string) $request->request->get('_token'))) {
			if ($request->isXmlHttpRequest()) {
				return new JsonResponse(['ok' => false, 'error' => 'invalid_csrf'], 400);
			}
			$this->addFlash('error', 'Nieprawidlowy token formularza.');
			return $this->redirectToRoute('wishlist_show');
		}

		$wishlist = $wishlistRepository->findOneByUser($user);
		if (!$wishlist) {
			if ($request->isXmlHttpRequest()) {
				return new JsonResponse(['ok' => false, 'error' => 'wishlist_not_found'], 404);
			}
			throw $this->createNotFoundException('Wishlist nie istnieje dla tego konta.');
		}

		$wishlist->removeProduct($product);
		$em->flush();

		if ($request->isXmlHttpRequest()) {
			return new JsonResponse([
				'ok' => true,
				'action' => 'remove',
				'productId' => $product->getId(),
				'toggleHtml' => $this->renderView('wishlist/_toggle_button.html.twig', [
					'product' => $product,
					'in_wishlist' => false,
				]),
			]);
		}

		return $this->redirect($request->headers->get('referer') ?: $this->generateUrl('wishlist_show'));
	}
}
