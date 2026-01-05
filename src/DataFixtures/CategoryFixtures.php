<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class CategoryFixtures extends Fixture implements FixtureGroupInterface
{
    public const CATEGORY_REFERENCE = 'category';

    public function load(ObjectManager $manager): void
    {
        $categories = [
            'Entrées',
            'Plats',
            'Desserts',
            'Boissons',
            'Végétarien',
            'Vegan',
            'Poissons',
            'Viandes',
            'Menus enfants',
            'Spécialités du chef',
        ];

        foreach ($categories as $index => $title) {
            $category = (new Category())
                ->setTitle($title);

            $manager->persist($category);

            // Référence pour les autres fixtures
            $this->addReference(self::CATEGORY_REFERENCE . ($index + 1), $category);
        }

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['restau'];
    }
}
