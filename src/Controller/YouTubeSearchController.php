<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/youtube')]
class YouTubeSearchController extends AbstractController
{
    #[Route('/search', name: 'youtube_search')]
    public function search(Request $request): Response
    {
        return $this->render('recoveryplan/search.html.twig');
    }
}