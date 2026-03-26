<?php

namespace App\DataFixtures;
use App\Entity\Event;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class EventFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
         for ($i = 1; $i <= 5; $i++) {
            $event = new Event();

            $event->setTitle("Event $i");
            $event->setDescription("Description de l'événement $i: trés intéressant et à ne pas manquer !");
            $event->setDate(new \DateTime("+$i days"));
            $event->setLocation("Sousse, Tunisia");
            $event->setSeats(500);
            $event->setImage("https://www.addpinch.com/wp-content/uploads/2025/09/communities.jpg");

            $manager->persist($event);
        }

        $manager->flush();
    }
}
