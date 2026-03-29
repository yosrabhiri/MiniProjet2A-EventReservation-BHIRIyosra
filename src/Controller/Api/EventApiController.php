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
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

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
                'description' => $event->getDescription(),
                'image' => $event->getImage(),
            ];
        }

        return $this->json($data);
    }

   #[Route('/{id}/reserve', name: 'api_event_reserve', methods: ['POST'])]
public function reserve(
    Event $event,
    Request $request,
    EntityManagerInterface $entityManager,
    MailerInterface $mailer
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

    $data = json_decode($request->getContent(), true);

    $name = trim($data['name'] ?? '');
    $email = trim($data['email'] ?? '');
    $phone = trim($data['phone'] ?? '');

    if ($name === '' || $email === '' || $phone === '') {
        return $this->json([
            'message' => 'Les champs name, email et phone sont obligatoires'
        ], 400);
    }

    $reservation = new Reservation();
    $reservation->setName($name);
    $reservation->setEmail($email);
    $reservation->setPhone($phone);
    $reservation->setRelation($event);
    $reservation->setCreatedAt(new \DateTimeImmutable());

    $event->setSeats($event->getSeats() - 1);

    $entityManager->persist($reservation);
    $entityManager->persist($event);
    $entityManager->flush();

    $message = (new Email())
        ->from('yosrabhiri16@gmail.com')
        ->to($email)
        ->subject('Confirmation de réservation')
        ->html("
            <h2>Bonjour {$name},</h2>
            <p>Votre réservation a bien été confirmée.</p>
            <p><strong>Événement :</strong> {$event->getTitle()}</p>
            <p><strong>Date :</strong> ".$event->getDate()?->format('d/m/Y H:i')."</p>
            <p><strong>Lieu :</strong> {$event->getLocation()}</p>
            <p>Merci pour votre confiance.</p>
        ");

    try {
        $mailer->send($message);
    } catch (\Exception $e) {
        return $this->json([
            'message' => 'Réservation enregistrée, mais le mail n’a pas pu être envoyé.',
            'reservation' => [
                'id' => $reservation->getId(),
                'name' => $reservation->getName(),
                'email' => $reservation->getEmail(),
                'phone' => $reservation->getPhone(),
            ],
            'event' => [
                'id' => $event->getId(),
                'title' => $event->getTitle(),
                'remaining_seats' => $event->getSeats(),
            ]
        ], 201);
    }

    return $this->json([
        'message' => 'Réservation confirmée et email envoyé.',
        'reservation' => [
            'id' => $reservation->getId(),
            'name' => $reservation->getName(),
            'email' => $reservation->getEmail(),
            'phone' => $reservation->getPhone(),
        ],
        'event' => [
            'id' => $event->getId(),
            'title' => $event->getTitle(),
            'remaining_seats' => $event->getSeats(),
        ]
    ], 201);
}
}
