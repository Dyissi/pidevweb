<?php

namespace App\Controller;

use App\Service\ElevenLabsTTSService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

class TTSController extends AbstractController
{
    #[Route('/speak', name: 'app_speak')]
    public function speak(ElevenLabsTTSService $ttsService): Response
    {
        $text = "Hello, this is a test of the ElevenLabs Text-to-Speech API.";
        $audioFilePath = $ttsService->textToSpeech($text);

        return $this->file($audioFilePath);
    }
    #[Route('/speak-text', name: 'app_speak_text')]
    public function speakText(Request $request, ElevenLabsTTSService $ttsService): Response
    {
        $text = $request->query->get('text', '');
    
        if (empty($text)) {
            return new Response("No text provided.", 400);
        }
    
        $audioFilePath = $ttsService->textToSpeech($text);
    
        return $this->file($audioFilePath, null, ResponseHeaderBag::DISPOSITION_INLINE);

}
}

