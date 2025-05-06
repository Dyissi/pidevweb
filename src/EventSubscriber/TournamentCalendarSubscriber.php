<?php

namespace App\EventSubscriber;

use App\Entity\Tournament;
use App\Repository\TournamentRepository;
use CalendarBundle\CalendarEvents;
use CalendarBundle\Entity\Event;
use CalendarBundle\Event\CalendarEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class TournamentCalendarSubscriber implements EventSubscriberInterface
{
    private $tournamentRepository;
    private $router;

    public function __construct(TournamentRepository $tournamentRepository, UrlGeneratorInterface $router)
    {
        $this->tournamentRepository = $tournamentRepository;
        $this->router = $router;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CalendarEvents::SET_DATA => 'onCalendarSetData',
        ];
    }

    public function onCalendarSetData(CalendarEvent $calendar): void
    {
        $start = $calendar->getStart();
        $end = $calendar->getEnd();
        $filters = $calendar->getFilters();

        $tournaments = $this->tournamentRepository
            ->createQueryBuilder('t')
            ->where('t.tournamentStartDate BETWEEN :start AND :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getResult();

        foreach ($tournaments as $tournament) {
            $event = new Event(
                $tournament->getTournamentName(),
                $tournament->getTournamentStartDate(),
                $tournament->getTournamentStartDate() // Single-day event
            );
            $event->setOptions([
                'backgroundColor' => '#1a3c34',
                'borderColor' => '#1a3c34',
            ]);
            $event->addOption(
                'url',
                $this->router->generate('app_tournament_show', ['id' => $tournament->getId()])
            );
            $calendar->addEvent($event);
        }
    }
}