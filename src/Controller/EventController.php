<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Event;
use App\Repository\EventRepository;
use App\Entity\Reservation;
use App\Form\ReservationType;
use Doctrine\ORM\EntityManagerInterface;



final class EventController extends AbstractController
{
    #[Route('/event', name: 'app_event')]
    public function index(Request $request, EventRepository $eventRepository): Response
    {
        $location = $request->query->get('location');
        $date = $request->query->get('date');
        $seats = $request->query->get('seats');

        $events = $eventRepository->searchEvents($location, $date, $seats);

        return $this->render('event/index.html.twig', [
            'events' => $events,
        ]);
    }
   #[Route('/events/{id}/reserve', name: 'event_reserve')]
public function reserve(
    Event $event,
    Request $request,
    EntityManagerInterface $entityManager
): Response {
    if ($event->getSeats() <= 0) {
        $this->addFlash('danger', 'Cet événement est complet.');

        return $this->redirectToRoute('app_event');
    }

    $reservation = new Reservation();
    $reservation->setRelation($event);
    $reservation->setCreatedAt(new \DateTimeImmutable());

    $form = $this->createForm(ReservationType::class, $reservation);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $event->setSeats($event->getSeats() - 1);

        $entityManager->persist($reservation);
        $entityManager->persist($event);
        $entityManager->flush();

        $this->addFlash('success', 'Réservation confirmée pour l\'événement : ' . $event->getTitle());

        return $this->redirectToRoute('app_event');
    }

    return $this->render('event/reserve.html.twig', [
        'event' => $event,
        'form' => $form->createView(),
    ]);
}
}
