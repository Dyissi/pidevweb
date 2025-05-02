<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class GameController extends AbstractController
{
    #[Route('/game', name: 'app_game')]
    public function game(): Response
    {
        return $this->render('recoveryplan/game.html.twig');
    }
}