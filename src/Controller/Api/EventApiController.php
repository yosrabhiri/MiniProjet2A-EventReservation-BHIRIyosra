<?php

namespace App\Controller\Api;

use App\Entity\Event;
use App\Entity\Reservation;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/events')]
final class EventApiController extends AbstractController
{
    #[Route('', name: 'api_events_list', methods: ['GET'])]
    public function index(Request $request, EventRepository $eventRepository): JsonResponse
    {
        $location = $request->query->get('location');
        $date = $request->query->get('date');
        $seats = $request->query->get('seats');

        $events = $eventRepository->searchEvents($location, $date, $seats);

        $data = [];

        foreach ($events as $event) {
            $data[] = [
                'id' => $event->getId(),
                'title' => $event->getTitle(),
                'location' => $event->getLocation(),
                'date' => $event->getDate()?->format('Y-m-d H:i:s'),
                'seats' => $event->getSeats(),
            ];
        }

        return $this->json($data);
    }

    #[Route('/{id}/reserve', name: 'api_event_reserve', methods: ['POST'])]
    public function reserve(
        Event $event,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $user = $this->getUser();

        if (!$user) {
            return $this->json([
                'message' => 'Non authentifié'
            ], 401);
        }

        if ($event->getSeats() <= 0) {
            return $this->json([
                'message' => 'Cet événement est complet'
            ], 400);
        }

        $reservation = new Reservation();
        $reservation->setRelation($event);
        $reservation->setCreatedAt(new \DateTimeImmutable());

        // si ton entité Reservation a une relation avec User :
        // $reservation->setUser($user);

        $event->setSeats($event->getSeats() - 1);

        $entityManager->persist($reservation);
        $entityManager->persist($event);
        $entityManager->flush();

        return $this->json([
            'message' => 'Réservation confirmée',
            'event' => [
                'id' => $event->getId(),
                'title' => $event->getTitle(),
                'remaining_seats' => $event->getSeats(),
            ]
        ]);
    }
}
