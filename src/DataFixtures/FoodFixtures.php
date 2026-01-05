<?php

namespace App\DataFixtures;

use App\Entity\Food;
use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class FoodFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public const FOOD_REFERENCE = 'food_';

    public function load(ObjectManager $manager): void
    {
        $foods = [
            [
                'title' => 'Salade fraîche du jardin',
                'description' => 'Salade verte, légumes croquants, vinaigrette maison.',
                'price' => 9,
            ],
            [
                'title' => 'Burger du chef',
                'description' => 'Bœuf français, cheddar affiné, pain brioché.',
                'price' => 15,
            ],
            [
                'title' => 'Pavé de saumon grillé',
                'description' => 'Saumon grillé, sauce citronnée, légumes de saison.',
                'price' => 18,
            ],
            [
                'title' => 'Pâtes végétariennes',
                'description' => 'Pâtes fraîches, légumes rôtis, pesto maison.',
                'price' => 13,
            ],
            [
                'title' => 'Fondant au chocolat',
                'description' => 'Chocolat noir, cœur coulant, crème anglaise.',
                'price' => 8,
            ],
        ];

        foreach ($foods as $index => $data) {
            $food = (new Food())
                ->setTitle($data['title'])
                ->setDescription($data['description'])
                ->setPrice($data['price']);

            // Associe 1 à 3 catégories aléatoires
            $categoriesCount = random_int(1, 3);
            $usedIndexes = [];

            for ($i = 0; $i < $categoriesCount; $i++) {
                do {
                    $categoryIndex = random_int(1, 10);
                } while (in_array($categoryIndex, $usedIndexes, true));

                $usedIndexes[] = $categoryIndex;

                /** @var Category $category */
                $category = $this->getReference(
                    CategoryFixtures::CATEGORY_REFERENCE . $categoryIndex,
                    Category::class
                );

                $food->addCategory($category);
            }

            $manager->persist($food);

            // Référence pour d’autres fixtures (menus, commandes, etc.)
            $this->addReference(self::FOOD_REFERENCE . ($index + 1), $food);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            CategoryFixtures::class,
        ];
    }

    public static function getGroups(): array
    {
        return ['restau'];
    }
}
