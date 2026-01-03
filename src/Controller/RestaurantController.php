<?php

namespace App\Controller;

use App\Repository\RestaurantRepository;
use App\Entity\Restaurant;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/restaurant', name: 'app_api_restaurant_')]
class RestaurantController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private RestaurantRepository $repository,
        private SerializerInterface $serializer,
        private UrlGeneratorInterface $urlGenerator,
        )
    {

    }
    #[Route('/', name: 'new', methods: ['POST'])]
    #[OA\Post(
        path: '/api/restaurant/',
        summary: 'Créer un nouveau restaurant',
        requestBody: new OA\RequestBody(
            required: true,
            description: 'Données du restaurant à créer',
            content: new OA\JsonContent(
                type: 'object',
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Nom du restaurant'),
                    new OA\Property(property: 'description', type: 'string', example: 'Description du restaurant'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Restaurant créé avec succès',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'name', type: 'string', example: 'Nom du restaurant'),
                        new OA\Property(property: 'description', type: 'string', example: 'Description du restaurant'),
                        new OA\Property(property: 'createdAt', type: 'string', format: 'date-time', example: '2026-01-03T12:00:00+01:00'),
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Requête invalide (JSON invalide ou champs manquants)'
            ),
            new OA\Response(
                response: 401,
                description: 'Non authentifié'
            ),
            new OA\Response(
                response: 403,
                description: 'Accès refusé'
            ),
        ]
    )]
    public function new(Request $request): JsonResponse
    {
        $restaurant = $this->serializer->deserialize($request->getContent(), Restaurant::class, 'json');
        $restaurant->setCreatedAt(new DateTimeImmutable());

        $this->em->persist($restaurant);
        $this->em->flush();

        $responseData = $this->serializer->serialize($restaurant, 'json');
        $location = $this->urlGenerator->generate(
            'app_api_restaurant_show',
            ['id' => $restaurant->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );
        
        return new JsonResponse($responseData, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'],  requirements: ['id' => '\d+'])]
    #[OA\Get(
        path: '/api/restaurant/{id}',
        summary: 'Afficher un restaurant par ID',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'ID du restaurant à afficher',
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Restaurant trouvé avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'name', type: 'string', example: 'Nom du restaurant'),
                        new OA\Property(property: 'description', type: 'string', example: 'Description du restaurant'),
                        new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Restaurant non trouvé'
            ),
        ]
    )]
    public function show(int $id): JsonResponse
    {
        $restaurant = $this->repository->findOneBy(['id' => $id]);

    if ($restaurant) {
            $responseData = $this->serializer->serialize($restaurant, 'json');

            return new JsonResponse($responseData, Response::HTTP_OK,[], true);
        }
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    #[Route('/{id}', name: 'edit', methods: ['PUT'], requirements: ['id' => '\d+'])]
    #[OA\Put(
        path: '/api/restaurant/{id}',
        summary: 'Mettre à jour un restaurant par ID',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'ID du restaurant à modifier',
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            description: 'Données du restaurant à mettre à jour',
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Nom du restaurant'),
                    new OA\Property(property: 'description', type: 'string', example: 'Description du restaurant'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 204,
                description: 'Restaurant mis à jour (aucun contenu retourné)'
            ),
            new OA\Response(
                response: 404,
                description: 'Restaurant non trouvé'
            ),
            new OA\Response(
                response: 400,
                description: 'Requête invalide (JSON invalide)'
            ),
            new OA\Response(
                response: 401,
                description: 'Non authentifié'
            ),
            new OA\Response(
                response: 403,
                description: 'Accès refusé'
            ),
        ]
    )]
    public function edit(int $id, Request $request): JsonResponse
    {
        $restaurant = $this->repository->findOneBy(['id' => $id]);

        if ($restaurant) {
            $restaurant = $this->serializer->deserialize(
                $request->getContent(),
                Restaurant::class,
                'json',
                [AbstractNormalizer::OBJECT_TO_POPULATE => $restaurant]
            );

            $restaurant->setUpdatedAt(new DateTimeImmutable());
            $this-> em->flush();

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }
        
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    #[Route('/{id}', name: 'delete', methods: 'DELETE')]
    #[OA\Delete(
        path: '/api/restaurant/{id}',
        summary: 'Supprimer un restaurant par ID',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'ID du restaurant à supprimer',
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Restaurant supprimé avec succès (aucun contenu retourné)'
            ),
            new OA\Response(
                response: 404,
                description: 'Restaurant non trouvé'
            ),
            new OA\Response(
                response: 401,
                description: 'Non authentifié'
            ),
            new OA\Response(
                response: 403,
                description: 'Accès refusé'
            ),
        ]
    )]
    public function delete(int $id): JsonResponse
    {
        $restaurant = $this ->repository->findOneBy(['id' => $id]);

        if ($restaurant){
            $this->em->remove($restaurant);
            $this->em->flush();

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }

        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }
}
