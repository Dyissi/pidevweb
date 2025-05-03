<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpClient\HttpClient;

#[Route('/api/profile-image')]
class ProfileImageApiController extends AbstractController
{
    private LoggerInterface $logger;
    private Filesystem $filesystem;

    public function __construct(LoggerInterface $logger, Filesystem $filesystem)
    {
        $this->logger = $logger;
        $this->filesystem = $filesystem;
    }

    #[Route('/upload', name: 'api_profile_image_upload', methods: ['POST'])]
    public function upload(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return new JsonResponse(['error' => 'Unauthorized'], 401);
        }

        $file = $request->files->get('image');
        $isAvatar = $request->request->get('transform_to_avatar', false);

        if (!$file instanceof UploadedFile) {
            return new JsonResponse(['error' => 'No image uploaded'], 400);
        }

        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/profile_images';
        $this->filesystem->mkdir($uploadDir);

        if ($isAvatar) {
            try {
                $client = HttpClient::create();
                $response = $client->request('POST', 'https://api.deepai.org/api/toonify', [
                    'headers' => [
                        'api-key' => $_ENV['DEEPAI_API_KEY'],
                    ],
                    'body' => [
                        'image' => base64_encode(file_get_contents($file->getPathname())),
                    ],
                ]);

                if ($response->getStatusCode() === 200) {
                    $avatarData = $response->toArray();
                    $avatarUrl = $avatarData['output_url'];
                    $avatarFileName = uniqid('avatar_') . '.png';
                    $avatarPath = $uploadDir . '/' . $avatarFileName;
                    $this->filesystem->dumpFile($avatarPath, file_get_contents($avatarUrl));
                    $imageUrl = '/uploads/profile_images/' . $avatarFileName;
                } else {
                    return new JsonResponse(['error' => 'Failed to transform image'], 500);
                }
            } catch (\Exception $e) {
                $this->logger->error('Avatar transformation failed: ' . $e->getMessage());
                return new JsonResponse(['error' => 'Avatar transformation failed'], 500);
            }
        } else {
            $fileName = uniqid('profile_') . '.' . $file->guessExtension();
            $file->move($uploadDir, $fileName);
            $imageUrl = '/uploads/profile_images/' . $fileName;
        }

        $user->setProfileImageUrl($imageUrl);
        $entityManager->flush();

        return new JsonResponse(['image_url' => $imageUrl], 200);
    }
}