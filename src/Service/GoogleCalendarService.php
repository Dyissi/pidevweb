<?php

   namespace App\Service;

   use Google\Client as Google_Client;
   use Google\Service\Calendar as Google_Service_Calendar;
   use Google\Service\Calendar\Event as Google_Service_Calendar_Event;
   use Psr\Log\LoggerInterface;

   class GoogleCalendarService
   {
       private Google_Client $client;
       private LoggerInterface $logger;

       public function __construct(string $clientId, string $clientSecret, string $redirectUri, LoggerInterface $logger)
       {
           $this->client = new Google_Client();
           $this->client->setClientId($clientId);
           $this->client->setClientSecret($clientSecret);
           $this->client->setRedirectUri($redirectUri);
           $this->client->addScope(Google_Service_Calendar::CALENDAR);
           $this->client->setAccessType('offline');
           $this->logger = $logger;
       }

       public function getClient(): Google_Client
       {
           return $this->client;
       }

       public function createAuthUrl(): string
       {
           return $this->client->createAuthUrl();
       }

       public function setAccessToken(string $code): void
       {
           try {
               $accessToken = $this->client->fetchAccessTokenWithAuthCode($code);
               if (isset($accessToken['error'])) {
                   $this->logger->error('OAuth error: ' . json_encode($accessToken));
                   throw new \Exception('Failed to fetch access token: ' . $accessToken['error']);
               }
               $this->client->setAccessToken($accessToken);
           } catch (\Exception $e) {
               $this->logger->error('Error setting access token: ' . $e->getMessage());
               throw $e;
           }
       }

       public function createEvent(string $calendarId, string $summary, \DateTimeInterface $start, \DateTimeInterface $end): string
       {
           $service = new Google_Service_Calendar($this->client);
           
           $endDate = new \DateTime($end->format('Y-m-d'));
           $endDate->modify('+1 day');
           
           $event = new Google_Service_Calendar_Event([
               'summary' => $summary,
               'start' => [
                   'date' => $start->format('Y-m-d'),
               ],
               'end' => [
                   'date' => $endDate->format('Y-m-d'),
               ],
           ]);

           $event = $service->events->insert($calendarId, $event);
           return $event->getId();
       }

       public function getEvents(string $calendarId, \DateTimeInterface $timeMin, \DateTimeInterface $timeMax): array
       {
           $service = new Google_Service_Calendar($this->client);
           $events = $service->events->listEvents($calendarId, [
               'timeMin' => $timeMin->format(\DateTime::RFC3339),
               'timeMax' => $timeMax->format(\DateTime::RFC3339),
           ]);
           return $events->getItems();
       }
   }