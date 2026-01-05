<?php

namespace App\DataFixtures;

use App\Entity\Booking;
use App\Entity\Restaurant;
use DateInterval;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Exception;

class BookingFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public const BOOKING_REFERENCE = 'booking_';

    /** @throws Exception */
    public function load(ObjectManager $manager): void
    {
        $allergies = [
            null,
            'Gluten',
            'Arachides',
            'Lactose',
            'Fruits à coque',
            'Œufs',
            'Poisson',
            'Soja',
        ];

        // Créons 40 réservations
        for ($i = 1; $i <= 40; $i++) {
            /** @var Restaurant $restaurant */
            $restaurant = $this->getReference(
                RestaurantFixtures::RESTAURANT_REFERENCE . random_int(1, 20),
                Restaurant::class
            );

            // Date entre aujourd’hui et +30 jours
            $date = (new DateTime('today'))->add(new DateInterval('P' . random_int(0, 30) . 'D'));

            // Heure entre 12:00-14:00 ou 19:00-22:00
            $isLunch = (bool) random_int(0, 1);

            if ($isLunch) {
                $hour = random_int(12, 14);
                $minute = [0, 15, 30, 45][random_int(0, 3)];
            } else {
                $hour = random_int(19, 22);
                $minute = [0, 15, 30, 45][random_int(0, 3)];
            }

            $orderHour = (new DateTime())
                ->setTime($hour, $minute, 0);

            $booking = (new Booking())
                ->setGuestNumber(random_int(1, 8))
                ->setOrderDate($date)
                ->setOrderHour($orderHour)
                ->setAllergy($allergies[random_int(0, count($allergies) - 1)])
                ->setRestaurant($restaurant);

            $manager->persist($booking);

            $this->addReference(self::BOOKING_REFERENCE . $i, $booking);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            RestaurantFixtures::class,
        ];
    }

    public static function getGroups(): array
    {
        return ['restau'];
    }
}
