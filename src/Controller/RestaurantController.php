<?php

namespace App\Controller;

use App\Repository\RestaurantRepository;
use App\Entity\Restaurant;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/restaurant', name: 'app_api_restaurant_')]
class RestaurantController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em, private RestaurantRepository $repository)
    {

    }
    #[Route('/new', name: 'new', methods: ['POST'])]
    public function new(): Response
    {
        $restaurant = new Restaurant();
        $restaurant-> setName('Quai Antique');
        $restaurant-> setDescription('Le goût des choses bien faites par le chef Arnaud Michant.');
        $restaurant-> setMaxGuest(40);
        $restaurant-> setCreatedAt(new \DateTimeImmutable());

        //On stock en Base et on averti l'utilisateur
        
        $this-> em->persist($restaurant);
        $this-> em->flush();

        return $this->json(
            ['message' => "Le Restaurant a été crée avec l'id {$restaurant->getId()}"], Response::HTTP_CREATED,
        );
    }

    #[Route('/{id}', name: 'show', methods: ['GET'],  requirements: ['id' => '\d+'])]
    public function show(int $id): Response
    {
        $restaurant = $this->repository->findOneBy(['id' => $id]);

    if (!$restaurant) {
            throw $this->createNotFoundException("Aucun Restaurant trouvé pour l'id {$id}");
        }
        return $this->json(
            ['message' => "Un Restaurant a été trouvé : {$restaurant->getName()} pour id : {$restaurant->getId()}"]
        );
    }

    #[Route('/{id}', name: 'edit', methods: ['PUT'], requirements: ['id' => '\d+'])]
    public function edit(int $id): Response
    {
        $restaurant = $this->repository->findOneBy(['id' => $id]);

        if (!$restaurant) {
            throw $this->createNotFoundException("Pas de Restaurant trouvé pour l'id {$id}");
        }

        $restaurant->setName('Nom du Restaurant update');
        $this-> em->flush();
        
        return $this->redirectToRoute('app_api_restaurant_show', ['id' => $restaurant->getId()]);
    }

    #[Route('/{id}', name: 'delete', methods: 'DELETE')]
    public function delete(int $id): Response
    {
        $restaurant = $this ->repository->findOneBy(['id' => $id]);

        if (!$restaurant){
            throw $this->createNotFoundException("Pas de Restaurant trouvé pour l'id {$id}");
        }

        $this->em->remove($restaurant);
        $this->em->flush();

        return $this->json(['message' => "Le restaurant a été supprimé"], Response::HTTP_NO_CONTENT);
    }
}
