<?php

declare(strict_types=1);

namespace App\UI\Controller;

use App\Application\Service\PriceQueryService;
use App\UI\Security\ApiKeyAuthenticator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class PriceController extends AbstractController
{
    private PriceQueryService $priceQueryService;
    private ApiKeyAuthenticator $authenticator;

    public function __construct(
        PriceQueryService $priceQueryService,
        ApiKeyAuthenticator $authenticator,
    ) {
        $this->priceQueryService = $priceQueryService;
        $this->authenticator = $authenticator;
    }

    /**
     * @throws \Exception
     */
    #[Route('/prices', methods: ['GET'])]
    public function getAllPrices(Request $request): JsonResponse
    {
        if (!$this->authenticator->authenticate($request)) {
            return new JsonResponse(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $prices = $this->priceQueryService->getAllLowestPrices();

        $data = array_map(
            fn ($priceResponse) => $priceResponse->toArray(),
            $prices
        );

        return new JsonResponse($data);
    }

    #[Route('/prices/{id}', methods: ['GET'])]
    public function getPriceById(Request $request, string $id): JsonResponse
    {
        if (!$this->authenticator->authenticate($request)) {
            return new JsonResponse(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $priceResponse = $this->priceQueryService->getLowestPriceForProduct($id);

        if (!$priceResponse) {
            return new JsonResponse(['error' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($priceResponse->toArray());
    }
}
