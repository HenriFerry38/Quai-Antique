<?php

namespace App\Controller;

use App\Entity\Food;
use App\Repository\FoodRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/food', name: 'app_api_food_')]
class FoodController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em, private FoodRepository $repository)
    {

    }
    #[Route('/new', name: 'new', methods: ['POST'])]
    public function new(): Response
    {
        $food = new Food();
        $food-> setTitle('Poulet Roti');
        $food-> setDescription('1/2 Poulet Roti servie avec frite et salade');
        $food-> setPrice(12);
        $food-> setCreatedAt(new \DateTimeImmutable());

        //On stock en Base et on averti l'utilisateur
        
        $this-> em->persist($food);
        $this-> em->flush();

        return $this->json(
            ['message' => "Le plat a été crée avec l'id {$food->getId()}"], Response::HTTP_CREATED,
        );
    }

    #[Route('/{id}', name: 'show', methods: ['GET'],  requirements: ['id' => '\d+'])]
    public function show(int $id): Response
    {
        $food = $this->repository->findOneBy(['id' => $id]);

    if (!$food) {
            throw $this->createNotFoundException("Aucun plat trouvé pour l'id {$id}");
        }
        return $this->json(
            ['message' => "Un plat a été trouvé : {$food->getTitle()} pour id : {$food->getId()}"]
        );
    }

    #[Route('/{id}', name: 'edit', methods: ['PUT'], requirements: ['id' => '\d+'])]
    public function edit(int $id): Response
    {
        $food = $this->repository->findOneBy(['id' => $id]);

        if (!$food) {
            throw $this->createNotFoundException("Pas de plat trouvé pour l'id {$id}");
        }

        $food->setTitle('Nom du plat update');
        $this-> em->flush();
        
        return $this->redirectToRoute('app_api_food_show', ['id' => $food->getId()]);
    }

    #[Route('/{id}', name: 'delete', methods: 'DELETE')]
    public function delete(int $id): Response
    {
        $food = $this ->repository->findOneBy(['id' => $id]);

        if (!$food){
            throw $this->createNotFoundException("Pas de plat trouvé pour l'id {$id}");
        }

        $this->em->remove($food);
        $this->em->flush();

        return $this->json(['message' => "Le plat a été supprimé"], Response::HTTP_NO_CONTENT);
    }
}