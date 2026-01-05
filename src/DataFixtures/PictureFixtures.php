<?php
namespace App\DataFixtures;
use App\Entity\Restaurant;
use App\Service\Utils;
use App\Entity\Picture;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Exception;


class PictureFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    /** @throws Exception */
    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= 20; $i++) {
            /** @var Restaurant $restaurant */
            $restaurant = $this->getReference(RestaurantFixtures::RESTAURANT_REFERENCE . random_int(1, 20), Restaurant::class);
            $title = "Article n°$i";
            $picture = (new Picture())
                ->setTitle($title)
                ->setSlug("slug")
                ->setRestaurant($restaurant)
                ->setCreatedAt(new DateTimeImmutable());
            $manager->persist($picture);
        }
        $manager->flush();
    }
    
    public function getDependencies(): array
    {
        return [RestaurantFixtures::class];
    }

    public static function getGroups(): array
    {
        return ['restau'];
    }
}