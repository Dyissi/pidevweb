<?php

namespace App\Controller;

use App\Entity\Claim;
use App\Form\ClaimType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;
use Knp\Component\Pager\PaginatorInterface;
use League\Csv\Writer;
use Symfony\Component\Notifier\ChatterInterface;
use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Component\Notifier\Bridge\Mercure\MercureOptions;
use Psr\Log\LoggerInterface;
use Symfony\UX\Turbo\TurboStreamResponse;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use App\Service\SentimentService;
use Symfony\Component\Form\FormError;
use App\Service\AssemblyAIService;
use Symfony\Component\HttpFoundation\JsonResponse;




#[Route('/claim')]
final class ClaimController extends AbstractController
{
    #[Route('', name: 'app_claim_index', methods: ['GET'])]
    public function index(
        Request $request,
        EntityManagerInterface $entityManager,
        PaginatorInterface $paginator,
        SentimentService $sentimentService // ✅ Inject it here
    ): Response {
        $sort = $request->query->get('sort', 'c.claimDate');
        $direction = strtoupper($request->query->get('direction', 'ASC'));
        $currentStatus = $request->query->get('status');
        $currentCategory = $request->query->get('category');
        $currentSubmitter = $request->query->get('submitter');
    
        $queryBuilder = $entityManager->getRepository(Claim::class)
            ->findSortedFilteredQuery($currentStatus, $currentCategory, $currentSubmitter, $sort, $direction);
    
        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            5,
            [
                'defaultSortFieldName' => 'c.claimDate',
                'defaultSortDirection' => 'ASC',
                'sortFieldWhitelist' => ['c.claimDescription', 'c.claimStatus', 'c.claimDate', 'c.claimCategory'],
            ]
        );
    
        // ✅ Run sentiment analysis on each visible claim
        $claimSentiments = [];
        foreach ($pagination as $claim) {
            $fullResult = $sentimentService->analyzeSentiment($claim->getClaimDescription());
            $claimSentiments[$claim->getClaimId()] = $fullResult[0][0]; // ✅ Pick top label from first result
        }
    
        return $this->render('claim/index.html.twig', [
            'claims' => $pagination,
            'sentiments' => $claimSentiments, // ✅ pass to Twig
            'currentSort' => str_replace('c.', '', $sort),
            'currentDirection' => $direction,
            'currentStatus' => $currentStatus,
            'currentCategory' => $currentCategory,
            'currentSubmitter' => $currentSubmitter,
        ]);
    }

    #[Route('/new', name: 'app_claim_new', methods: ['GET', 'POST'])]
public function new(
    Request $request,
    EntityManagerInterface $entityManager,
    Security $security,
    \App\Service\BadWordsService $badWordsService
): Response {
    $user = $security->getUser();

    $claim = new Claim();
    $claim->setClaimDate(new \DateTime());
    $claim->setClaimStatus('In Review');
    $claim->setIdUser($user);

    $form = $this->createForm(ClaimType::class, $claim, ['is_edit' => false]);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $description = $form->get('claimDescription')->getData();

        if ($badWordsService->containsBadWords($description)) {
            $form->get('claimDescription')->addError(new \Symfony\Component\Form\FormError(
                'Your claim contains inappropriate language. Please revise.'
            ));
            $this->addFlash('error', 'Claim rejected due to inappropriate language.');
        } else {
            $entityManager->persist($claim);
            $entityManager->flush();

            $this->addFlash('success', 'Claim created successfully!');
            return $this->redirectToRoute('app_user_index');
        }
    }

    return $this->render('claim/new.html.twig', [
        'form' => $form->createView(),
    ]);
}

    

    #[Route('/{claimId<\d+>}', name: 'app_claim_show', methods: ['GET'])]
    public function show(int $claimId, EntityManagerInterface $entityManager): Response
    {
        $claim = $entityManager->getRepository(Claim::class)->find($claimId);

        if (!$claim) {
            throw $this->createNotFoundException('Claim not found.');
        }

        return $this->render('claim/show.html.twig', [
            'claim' => $claim,
        ]);
    }

    #[Route('/{claimId<\d+>}/edit', name: 'app_claim_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $claimId, EntityManagerInterface $entityManager): Response
    {
        $claim = $entityManager->getRepository(Claim::class)->find($claimId);

        if (!$claim) {
            throw $this->createNotFoundException('Claim not found.');
        }

        $form = $this->createForm(ClaimType::class, $claim, ['is_edit' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Claim updated successfully!');
            return $this->redirectToRoute('app_claim_index');
        }

        return $this->render('claim/edit.html.twig', [
            'claim' => $claim,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{claimId<\d+>}/delete', name: 'app_claim_delete', methods: ['POST'])]
    public function delete(Request $request, int $claimId, EntityManagerInterface $entityManager): Response
    {
        $claim = $entityManager->getRepository(Claim::class)->find($claimId);

        if (!$claim) {
            $this->addFlash('error', 'Claim not found.');
            return $this->redirectToRoute('app_claim_index');
        }

        if ($this->isCsrfTokenValid('delete' . $claim->getClaimId(), $request->request->get('_token'))) {
            $entityManager->remove($claim);
            $entityManager->flush();

            $this->addFlash('success', 'Claim deleted successfully!');
        } else {
            $this->addFlash('error', 'Invalid CSRF token.');
        }

        return $this->redirectToRoute('app_claim_index');
    }

  

   
    
    #[Route('/{claimId}/approve', name: 'app_claim_approve', methods: ['POST'])]
    public function approve(
        int $claimId,
        EntityManagerInterface $entityManager,
        ChatterInterface $chatter
    ): Response {
        $claim = $entityManager->getRepository(Claim::class)->find($claimId);
    
        if (!$claim) {
            return $this->json(['error' => 'Claim not found.'], 404);
        }
    
        $claim->setClaimStatus('Approved');
        $entityManager->flush();
    
        if ($user = $claim->getIdUser()) {
            $message = (new ChatMessage('✅ Your claim has been approved!'))
                ->transport('mercure')
                ->options(new MercureOptions(["/user/notifications/{$user->getId()}"]));
            $chatter->send($message);
        }
    
        return $this->redirectToRoute('app_claim_index');
    }
    
    #[Route('/{claimId}/reject', name: 'app_claim_reject', methods: ['POST'])]
public function reject(
    int $claimId,
    EntityManagerInterface $entityManager,
    ChatterInterface $chatter
): Response {
    $claim = $entityManager->getRepository(Claim::class)->find($claimId);

    if (!$claim) {
        return $this->json(['error' => 'Claim not found.'], 404);
    }

    $claim->setClaimStatus('Rejected');
    $entityManager->flush();

    if ($user = $claim->getIdUser()) {
        $message = (new ChatMessage('❌ Your claim has been rejected.'))
            ->transport('mercure')
            ->options(new MercureOptions(["/user/notifications/{$user->getId()}"]));
        $chatter->send($message);
    }

    return $this->redirectToRoute('app_claim_index');
}


    

    #[Route('/bulk-action', name: 'app_claim_bulk_action', methods: ['POST'])]
    public function bulkAction(Request $request, EntityManagerInterface $entityManager): Response
    {
        $selectedIds = $request->request->all('selected_claims'); // <-- use all() here
        $bulkAction = $request->request->get('bulk_action');
    
        if (empty($selectedIds) || !$bulkAction) {
            $this->addFlash('warning', 'Please select at least one claim and an action.');
            return $this->redirectToRoute('app_claim_index');
        }
    
        $claims = $entityManager->getRepository(Claim::class)->findBy([
            'claimId' => $selectedIds,
        ]);
    
        foreach ($claims as $claim) {
            switch ($bulkAction) {
                case 'approve':
                    $claim->setClaimStatus('Approved');
                    break;
                case 'reject':
                    $claim->setClaimStatus('Rejected');
                    break;
                case 'delete':
                    $entityManager->remove($claim);
                    break;
            }
        }
    
        $entityManager->flush();
    
        switch ($bulkAction) {
            case 'approve':
                $this->addFlash('success', 'Selected claims have been approved.');
                break;
            case 'reject':
                $this->addFlash('success', 'Selected claims have been rejected.');
                break;
            case 'delete':
                $this->addFlash('success', 'Selected claims have been deleted.');
                break;
        }
    
        return $this->redirectToRoute('app_claim_index');
    }

    #[Route('/claim/export', name: 'app_claim_export_csv', methods: ['GET'])]
public function exportCsv(Request $request, EntityManagerInterface $em): Response
{
    $claims = $em->getRepository(Claim::class)->findAll();

    $csv = Writer::createFromString('');
    $csv->insertOne(['Claim ID', 'Description', 'Status', 'Date', 'Category', 'Submitter', 'Target']);

    foreach ($claims as $claim) {
        $csv->insertOne([
            $claim->getClaimId(),
            $claim->getClaimDescription(),
            $claim->getClaimStatus(),
            $claim->getClaimDate()->format('Y-m-d'),
            $claim->getClaimCategory(),
            $claim->getIdUser() ? $claim->getIdUser()->getUserFname() . ' ' . $claim->getIdUser()->getUserLname() : '',
            $claim->getIdUserToClaim() ? $claim->getIdUserToClaim()->getUserFname() . ' ' . $claim->getIdUserToClaim()->getUserLname() : '',
        ]);
    }

    $now = (new \DateTime())->format('Y-m-d_H-i-s');
    $filename = 'claims-export-' . $now . '.csv'; // Exporting multiple claims

    $response = new Response((string) $csv);
    $response->headers->set('Content-Type', 'text/csv');
    $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

    return $response;
}


#[Route('/my-claims', name: 'app_my_claims', methods: ['GET'])]
public function myClaims(Request $request, EntityManagerInterface $entityManager, Security $security, PaginatorInterface $paginator): Response
{
    $user = $security->getUser(); // Get logged-in user

    $queryBuilder = $entityManager->getRepository(Claim::class)
        ->createQueryBuilder('c')
        ->where('c.id_user = :user')
        ->setParameter('user', $user)
        ->orderBy('c.claimDate', 'DESC');

    $pagination = $paginator->paginate(
        $queryBuilder,
        $request->query->getInt('page', 1),
        5
    );

    return $this->render('claim/my_claims.html.twig', [
        'claims' => $pagination,
    ]);
}

#[Route('/test-notification', name: 'test_notification')]
public function notifyUser(ChatterInterface $chatter): Response
{
    $userId = 5; // hardcode for testing

    $message = (new ChatMessage('🧪 Test message from backend!'))
    ->transport('mercure')
        ->options(new MercureOptions(["/user/notifications/$userId"]));

    $chatter->send($message);

    return new Response("Notification pushed to /user/notifications/$userId");
}

    



#[Route('/{claimId}/sentiment', name: 'app_claim_sentiment')]
public function sentiment(Claim $claim, SentimentService $sentimentService): Response
{
    $result = $sentimentService->analyzeSentiment($claim->getClaimDescription());

    return $this->json($result);
}

#[Route('/test-sentiment', name: 'test_sentiment')]
public function testSentiment(SentimentService $sentimentService): Response
{
    $text = "I am very disappointed with the service I received."; // 🔁 change this to test other inputs
    $result = $sentimentService->analyzeSentiment($text);

    return new Response('<pre>' . print_r($result, true) . '</pre>');
}

#[Route('/transcribe', name: 'app_assembly_transcribe', methods: ['POST'])]
public function transcribe(Request $request, AssemblyAIService $service, LoggerInterface $logger): JsonResponse
{
    $file = $request->files->get('audio');

    if (!$file) {
        $logger->error('❌ No audio file received in request.');
        return $this->json(['error' => 'No audio file received'], 400);
    }

    try {
        $path = $file->move(sys_get_temp_dir(), uniqid() . '.' . $file->guessExtension());
    } catch (\Throwable $e) {
        $logger->error('❌ File move failed: ' . $e->getMessage());
        return $this->json(['error' => 'File move failed'], 500);
    }

    $uploadUrl = $service->uploadAudio($path);
    if (!$uploadUrl) {
        $logger->error('❌ Upload failed.');
        return $this->json(['error' => 'Upload failed'], 500);
    }

    $text = $service->transcribe($uploadUrl);
    if (!$text) {
        $logger->error('❌ Transcription failed for URL: ' . $uploadUrl);
        return $this->json(['error' => 'Transcription failed', 'upload_url' => $uploadUrl], 500);
    }

    $logger->info('✅ Transcription succeeded.');
    return $this->json(['text' => $text]);
}



}
