<?php

namespace App\Controller;

use App\Repository\FoodRepository;
use App\Entity\Food;
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

#[Route('/api/food', name: 'app_api_food_')]
class FoodController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private FoodRepository $repository,
        private SerializerInterface $serializer,
        private UrlGeneratorInterface $urlGenerator,
        )
    {

    }
    #[Route('/', name: 'new', methods: ['POST'])]
    #[OA\Post(
        path: '/api/food/',
        summary: 'Créer un nouveau plat',
        requestBody: new OA\RequestBody(
            required: true,
            description: 'Données du plat à créer',
            content: new OA\JsonContent(
                type: 'object',
                required: ['title'],
                properties: [
                    new OA\Property(property: 'title', type: 'string', example: 'Burger maison'),
                    new OA\Property(property: 'description', type: 'string', example: 'Burger au bœuf avec fromage'),
                    new OA\Property(property: 'price', type: 'int', example: 12),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Plat créé avec succès',
                headers: [
                    new OA\Header(
                        header: 'Location',
                        description: 'URL du plat créé',
                        schema: new OA\Schema(
                            type: 'string',
                            format: 'uri',
                            example: 'http://localhost:8000/api/food/1'
                        )
                    ),
                ],
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'title', type: 'string', example: 'Burger maison'),
                        new OA\Property(property: 'description', type: 'string', example: 'Burger au bœuf avec fromage'),
                        new OA\Property(property: 'price', type: 'int', example: 12),
                        new OA\Property(property: 'createdAt', type: 'string', format: 'date-time', example: '2026-01-03T12:00:00+01:00'),
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Requête invalide (JSON incorrect)'
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
        $food = $this->serializer->deserialize($request->getContent(), Food::class, 'json');
        $food->setCreatedAt(new DateTimeImmutable());

        $this->em->persist($food);
        $this->em->flush();

        $responseData = $this->serializer->serialize($food, 'json');
        $location = $this->urlGenerator->generate(
            'app_api_restaurant_show',
            ['id' => $food->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );
        
        return new JsonResponse($responseData, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    #[OA\Get(
        path: '/api/food/{id}',
        summary: 'Afficher un plat par ID',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'ID du plat à afficher',
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Plat trouvé avec succès',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'name', type: 'string', example: 'Burger maison'),
                        new OA\Property(property: 'description', type: 'string', example: 'Burger au bœuf avec fromage'),
                        new OA\Property(property: 'price', type: 'int', example: 12),
                        new OA\Property(property: 'createdAt', type: 'string', format: 'date-time', example: '2026-01-03T12:00:00+01:00'),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Plat non trouvé'
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
    public function show(int $id): JsonResponse
    {
        $food = $this->repository->findOneBy(['id' => $id]);

    if ($food) {
            $responseData = $this->serializer->serialize($food, 'json');

            return new JsonResponse($responseData, Response::HTTP_OK,[], true);
        }
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    #[Route('/{id}', name: 'edit', methods: ['PUT'])]
    #[OA\Put(
        path: '/api/food/{id}',
        summary: 'Mettre à jour un plat par ID',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'ID du plat à modifier',
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            description: 'Données du plat à mettre à jour',
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Burger maison'),
                    new OA\Property(property: 'description', type: 'string', example: 'Burger au bœuf avec fromage'),
                    new OA\Property(property: 'price', type: 'int', example: 12),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 204,
                description: 'Plat mis à jour (aucun contenu retourné)'
            ),
            new OA\Response(
                response: 404,
                description: 'Plat non trouvé'
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
        $food = $this->repository->findOneBy(['id' => $id]);

        if ($food) {
            $food = $this->serializer->deserialize(
                $request->getContent(),
                Food::class,
                'json',
                [AbstractNormalizer::OBJECT_TO_POPULATE => $food]
            );

            $food->setUpdatedAt(new DateTimeImmutable());
            $this-> em->flush();

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }
        
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    #[Route('/{id}', name: 'delete', methods: 'DELETE')]
    #[OA\Delete(
        path: '/api/food/{id}',
        summary: 'Supprimer un plat par ID',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'ID du plat à supprimer',
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Plat supprimé avec succès (aucun contenu retourné)'
            ),
            new OA\Response(
                response: 404,
                description: 'Plat non trouvé'
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
        $food = $this ->repository->findOneBy(['id' => $id]);

        if ($food){
            $this->em->remove($food);
            $this->em->flush();

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }

        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }
}