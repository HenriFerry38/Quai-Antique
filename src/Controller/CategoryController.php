<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Entity\Category;
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

#[Route('/api/category', name: 'app_api_category_')]
class CategoryController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private CategoryRepository $repository,
        private SerializerInterface $serializer,
        private UrlGeneratorInterface $urlGenerator,
        )
    {

    }
    #[Route('/', name: 'new', methods: ['POST'])]
    #[OA\Post(
        path: '/api/category/',
        summary: 'Créer une nouvelle catégorie',
        requestBody: new OA\RequestBody(
            required: true,
            description: 'Données de la catégorie à créer',
            content: new OA\JsonContent(
                type: 'object',
                required: ['title'],
                properties: [
                    new OA\Property(property: 'title', type: 'string', example: 'Desserts')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Catégorie créée avec succès',
                headers: [
                    new OA\Header(
                        header: 'Location',
                        description: 'URL de la catégorie créée',
                        schema: new OA\Schema(
                            type: 'string',
                            format: 'uri',
                            example: 'http://localhost:8000/api/category/1'
                        )
                    ),
                ],
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'name', type: 'string', example: 'Desserts'),
                        new OA\Property(property: 'description', type: 'string', example: 'Plats sucrés et desserts'),
                        new OA\Property(
                            property: 'createdAt',
                            type: 'string',
                            format: 'date-time',
                            example: '2026-01-03T15:00:00+01:00'
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Requête invalide (JSON incorrect ou champs manquants)'
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
        $category = $this->serializer->deserialize($request->getContent(), Category::class, 'json');
        $category->setCreatedAt(new DateTimeImmutable());

        $this->em->persist($category);
        $this->em->flush();

        $responseData = $this->serializer->serialize($category, 'json');
        $location = $this->urlGenerator->generate(
            'app_api_category_show',
            ['id' => $category->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );
        
        return new JsonResponse($responseData, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    #[OA\Get(
        path: '/api/category/{id}',
        summary: 'Afficher une catégorie par ID',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'ID de la catégorie à afficher',
                schema: new OA\Schema(type: 'integer', minimum: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Catégorie trouvée avec succès',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'name', type: 'string', example: 'Desserts'),
                        new OA\Property(
                            property: 'createdAt',
                            type: 'string',
                            format: 'date-time',
                            example: '2026-01-03T15:00:00+01:00'
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Catégorie non trouvée'
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
        $category = $this->repository->findOneBy(['id' => $id]);

    if ($category) {
            $responseData = $this->serializer->serialize($category, 'json');

            return new JsonResponse($responseData, Response::HTTP_OK,[], true);
        }
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    #[Route('/{id}', name: 'edit', methods: ['PUT'])]
    #[OA\Put(
        path: '/api/category/{id}',
        summary: 'Mettre à jour une catégorie par ID',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'ID de la catégorie à modifier',
                schema: new OA\Schema(type: 'integer', minimum: 1)
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            description: 'Données de la catégorie à mettre à jour',
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'title', type: 'string', example: 'Desserts')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 204,
                description: 'Catégorie mise à jour (aucun contenu retourné)'
            ),
            new OA\Response(
                response: 404,
                description: 'Catégorie non trouvée'
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
        $category = $this->repository->findOneBy(['id' => $id]);

        if ($category) {
            $category = $this->serializer->deserialize(
                $request->getContent(),
                Category::class,
                'json',
                [AbstractNormalizer::OBJECT_TO_POPULATE => $category]
            );

            $category->setUpdatedAt(new DateTimeImmutable());
            $this-> em->flush();

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }
        
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    #[Route('/{id}', name: 'delete', methods: 'DELETE')]
    #[OA\Delete(
        path: '/api/category/{id}',
        summary: 'Supprimer une catégorie par ID',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'ID de la catégorie à supprimer',
                schema: new OA\Schema(type: 'integer', minimum: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Catégorie supprimée avec succès (aucun contenu retourné)'
            ),
            new OA\Response(
                response: 404,
                description: 'Catégorie non trouvée'
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
        $category = $this ->repository->findOneBy(['id' => $id]);

        if ($category){
            $this->em->remove($category);
            $this->em->flush();

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }

        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }
}