<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Menu;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class MenuFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public const MENU_REFERENCE = 'menu_';

    public function load(ObjectManager $manager): void
    {
        $menus = [
            [
                'title' => 'Menu Découverte',
                'description' => 'Un menu pour explorer les incontournables de la maison.',
                'price' => 25,
                'categories' => [1, 2, 3], // Entrées, Plats, Desserts
            ],
            [
                'title' => 'Menu Gourmand',
                'description' => 'Pour les grandes faims et les grands plaisirs.',
                'price' => 32,
                'categories' => [1, 2, 3],
            ],
            [
                'title' => 'Menu Végétarien',
                'description' => 'Des assiettes végétariennes pleines de goût.',
                'price' => 27,
                'categories' => [5, 1, 3], // Végétarien + Entrées + Desserts
            ],
            [
                'title' => 'Menu Enfant',
                'description' => 'Un menu adapté aux petits gourmands.',
                'price' => 12,
                'categories' => [9, 4, 3], // Enfants + Boissons + Desserts
            ],
            [
                'title' => 'Menu du Chef',
                'description' => 'Une sélection inspirée selon l’humeur du chef.',
                'price' => 38,
                'categories' => [10, 2, 3], // Spécialités + Plats + Desserts
            ],
        ];

        foreach ($menus as $index => $data) {
            $menu = (new Menu())
                ->setTitle($data['title'])
                ->setDescription($data['description'])
                ->setPrice($data['price']);

            // Ajout des catégories liées au menu
            foreach ($data['categories'] as $categoryIndex) {
                /** @var Category $category */
                $category = $this->getReference(
                    CategoryFixtures::CATEGORY_REFERENCE . $categoryIndex,
                    Category::class
                );

                $menu->addCategory($category);
            }

            $manager->persist($menu);

            // Référence pour d’autres fixtures / tests
            $this->addReference(self::MENU_REFERENCE . ($index + 1), $menu);
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
