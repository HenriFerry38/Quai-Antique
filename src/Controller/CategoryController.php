<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/category', name: 'app_api_category_')]
class CategoryController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em, private CategoryRepository $repository)
    {

    }
    #[Route('/new', name: 'new', methods: ['POST'])]
    public function new(): Response
    {
        $category = new Category();
        $category-> setTitle('Regime traditionel');
        $category-> setCreatedAt(new \DateTimeImmutable());

        //On stock en Base et on averti l'utilisateur
        
        $this-> em->persist($category);
        $this-> em->flush();

        return $this->json(
            ['message' => "La catégorie a été crée avec l'id {$category->getId()}"], Response::HTTP_CREATED,
        );
    }

    #[Route('/{id}', name: 'show', methods: ['GET'],  requirements: ['id' => '\d+'])]
    public function show(int $id): Response
    {
        $category = $this->repository->findOneBy(['id' => $id]);

    if (!$category) {
            throw $this->createNotFoundException("Aucune catégorie trouvée pour l'id {$id}");
        }
        return $this->json(
            ['message' => "Une catégorie a été trouvée : {$category->getTitle()} pour id : {$category->getId()}"]
        );
    }

    #[Route('/{id}', name: 'edit', methods: ['PUT'], requirements: ['id' => '\d+'])]
    public function edit(int $id): Response
    {
        $category = $this->repository->findOneBy(['id' => $id]);

        if (!$category) {
            throw $this->createNotFoundException("Pas de catégorie trouvée pour l'id {$id}");
        }

        $category->setTitle('Nom de la catégorie update');
        $this-> em->flush();
        
        return $this->redirectToRoute('app_api_category_show', ['id' => $category->getId()]);
    }

    #[Route('/{id}', name: 'delete', methods: 'DELETE')]
    public function delete(int $id): Response
    {
        $category = $this ->repository->findOneBy(['id' => $id]);

        if (!$category){
            throw $this->createNotFoundException("Pas de catégorie trouvé pour l'id {$id}");
        }

        $this->em->remove($category);
        $this->em->flush();

        return $this->json(['message' => "La catégorie a été supprimée"], Response::HTTP_NO_CONTENT);
    }
}