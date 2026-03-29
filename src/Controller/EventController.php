<?php

namespace App\Controller;

use App\Entity\Event;
use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class EventController extends AbstractController
{
    #[Route('/event', name: 'app_event', methods: ['GET'])]
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

    #[Route('/events/{id}/reserve', name: 'event_reserve', methods: ['GET'])]
    public function reserve(Event $event): Response
    {
        if ($event->getSeats() <= 0) {
            $this->addFlash('danger', 'Cet événement est complet.');

            return $this->redirectToRoute('app_event');
        }

        return $this->render('event/reserve.html.twig', [
            'event' => $event,
        ]);
    }
}
