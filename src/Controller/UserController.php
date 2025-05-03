<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Email;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface; 

#[Route('/user')]
class UserController extends AbstractController
{
    private $passwordHasher;
    private $mailer;
    private $logger;
    private $filesystem;
    private $csrfTokenManager; 

    public function __construct(
        UserPasswordHasherInterface $passwordHasher,
        MailerInterface $mailer,
        LoggerInterface $logger,
        Filesystem $filesystem,
        CsrfTokenManagerInterface $csrfTokenManager 
    ) {
        $this->passwordHasher = $passwordHasher;
        $this->mailer = $mailer;
        $this->logger = $logger;
        $this->filesystem = $filesystem;
        $this->csrfTokenManager = $csrfTokenManager;
    }

    #[Route('/profile', name: 'app_profile', methods: ['GET', 'POST'])]
    public function profile(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            $this->logger->error('No authenticated user found for profile access.');
            throw $this->createAccessDeniedException('You must be logged in.');
        }

        $role = strtolower($user->getUserRole() ?? 'athlete');
        if (!in_array($role, ['athlete', 'coach', 'med_staff'], true)) {
            $this->logger->warning('Invalid role detected', [
                'user' => $user->getUserIdentifier(),
                'role' => $role,
            ]);
            $role = 'athlete';
        }

        $this->logger->debug('Profile access', [
            'user' => $user->getUserIdentifier(),
            'role' => $role,
        ]);

        $form = $this->createForm(UserType::class, $user, [
            'is_new' => false,
            'role' => $role,
            'show_profile_image' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $this->logger->debug('Profile form submitted', [
                'valid' => $form->isValid(),
                'errors' => $form->getErrors(true)->__toString(),
                'transform_to_avatar' => $request->request->get('transform_to_avatar', false),
                'form_data' => $request->request->all(),
            ]);

            if ($form->isValid()) {
                $imageFile = $form->get('profileImage')->getData();
                $isAvatar = $request->request->get('transform_to_avatar', false);
                if ($imageFile) {
                    $imageUrl = $this->handleImageUpload($imageFile, $user, $isAvatar);
                    if ($imageUrl) {
                        $user->setProfileImageUrl($imageUrl);
                    }
                }

                $plainPassword = $form->get('user_pwd')->getData();
                if ($plainPassword) {
                    try {
                        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
                        $user->setUserPwd($hashedPassword);
                        $this->logger->debug('Password updated for user', ['user' => $user->getUserIdentifier()]);
                    } catch (\Exception $e) {
                        $this->logger->error('Failed to update password', [
                            'user' => $user->getUserIdentifier(),
                            'exception' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                        $this->addFlash('error', 'Password update failed: Contact support.');
                        return $this->render('user/profile.html.twig', [
                            'user' => $user,
                            'form' => $form->createView(),
                            'role' => $role,
                        ]);
                    }
                }

                try {
                    $entityManager->flush();
                    $this->addFlash('success', 'Your profile has been updated.');
                    $this->logger->info('Profile updated successfully', ['user' => $user->getUserIdentifier()]);
                    return $this->redirectToRoute('app_profile');
                } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
                    $this->addFlash('error', 'This email is already in use.');
                    $this->logger->error('Unique constraint violation in profile update', ['exception' => $e->getMessage()]);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'An error occurred while updating your profile.');
                    $this->logger->error('Unexpected error in profile update', [
                        'exception' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            } else {
                $this->addFlash('error', 'Please correct the errors in the form.');
            }
        }

        return $this->render('user/profile.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'role' => $role,
        ]);
    }

    private function handleImageUpload(?UploadedFile $file, User $user, bool $isAvatar = false): ?string
    {
        if (!$file) {
            $this->logger->debug('No file uploaded, returning existing URL', [
                'existing_url' => $user->getProfileImageUrl(),
            ]);
            return $user->getProfileImageUrl();
        }

        // Use DIRECTORY_SEPARATOR for Windows compatibility
        $uploadDir = $this->getParameter('kernel.project_dir') . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'profile_images';
        $this->logger->debug('Upload directory', ['path' => $uploadDir]);
        $this->filesystem->mkdir($uploadDir);

        if ($isAvatar) {
            try {
                // Use Robohash with userEmail as input
                $avatarInput = urlencode($user->getUserEmail());
                $robohashUrl = 'https://robohash.org/' . $avatarInput . '?set=set1&size=200x200';
                $this->logger->debug('Fetching Robohash avatar', ['url' => $robohashUrl]);
                $ch = curl_init($robohashUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                curl_setopt($ch, CURLOPT_HEADER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
                $response = curl_exec($ch);
                if ($response === false) {
                    $error = curl_error($ch);
                    $errno = curl_errno($ch);
                    $this->logger->error('Failed to fetch Robohash avatar', [
                        'email' => $user->getUserEmail(),
                        'error' => $error,
                        'errno' => $errno,
                    ]);
                    $this->addFlash('error', 'Failed to generate avatar: ' . $error);
                    curl_close($ch);
                    return null;
                }

                // Extract headers and body
                $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
                $headers = substr($response, 0, $headerSize);
                $imageContent = substr($response, $headerSize);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $this->logger->debug('Robohash response', [
                    'http_code' => $httpCode,
                    'headers' => $headers,
                    'content_length' => strlen($imageContent),
                ]);
                curl_close($ch);

                if ($httpCode !== 200) {
                    $this->logger->error('Robohash non-200 response', [
                        'http_code' => $httpCode,
                        'email' => $user->getUserEmail(),
                    ]);
                    $this->addFlash('error', 'Failed to generate avatar: HTTP ' . $httpCode);
                    return null;
                }

                if (empty($imageContent)) {
                    $this->logger->error('Empty Robohash response body', [
                        'email' => $user->getUserEmail(),
                    ]);
                    $this->addFlash('error', 'Failed to generate avatar: Empty response');
                    return null;
                }

                $avatarFileName = uniqid('avatar_') . '.png'; // Robohash uses PNG
                $avatarPath = $uploadDir . DIRECTORY_SEPARATOR . $avatarFileName;
                $this->logger->debug('Saving avatar', ['path' => $avatarPath]);
                $this->filesystem->dumpFile($avatarPath, $imageContent);
                $this->logger->debug('Avatar saved successfully', ['path' => $avatarPath]);
                return '/uploads/profile_images/' . $avatarFileName;
            } catch (\Exception $e) {
                $this->logger->error('Failed to generate avatar: ' . $e->getMessage(), [
                    'exception' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                $this->addFlash('error', 'Failed to generate avatar: ' . $e->getMessage());
                return null;
            }
        }

        $fileName = uniqid('profile_') . '.' . $file->guessExtension();
        $this->logger->debug('Saving uploaded image', ['filename' => $fileName]);
        $file->move($uploadDir, $fileName);
        $this->logger->debug('Uploaded image saved', ['path' => $uploadDir . DIRECTORY_SEPARATOR . $fileName]);
        return '/uploads/profile_images/' . $fileName;
    }

     #[Route('/new/{context}/{role}', name: 'app_user_new', methods: ['GET', 'POST'], defaults: ['context' => 'front', 'role' => 'athlete'], requirements: ['context' => 'front|back', 'role' => 'athlete|coach|med_staff'])]
    public function new(string $context, string $role, Request $request, EntityManagerInterface $entityManager): Response
    {
        $context = strtolower(trim($context));
        $role = strtolower(trim($role));

        $validFrontRoles = ['athlete'];
        $validBackRoles = ['coach', 'med_staff'];

        if ($context === 'front' && in_array($role, $validFrontRoles, true)) {
            if (!$this->isGranted('ROLE_COACH') && !$this->isGranted('ROLE_ADMIN')) {
                throw $this->createAccessDeniedException('Only coaches or admins can create athletes.');
            }
        } elseif ($context === 'back' && in_array($role, $validBackRoles, true)) {
            if (!$this->isGranted('ROLE_ADMIN')) {
                throw $this->createAccessDeniedException('Only admins can create coaches or medical staff.');
            }
        } else {
            throw $this->createAccessDeniedException('Invalid context or role.');
        }

        $user = new User();
        $form = $this->createForm(UserType::class, $user, ['is_new' => true, 'role' => $role]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $this->logger->debug('User creation form submitted', [
                'valid' => $form->isValid(),
                'errors' => $form->getErrors(true)->__toString(),
                'form_data' => $request->request->all(),
                'mailer_dsn' => getenv('MAILER_DSN'),
            ]);

            if ($form->isValid()) {
                $plainPassword = $form->get('user_pwd')->getData();
                if ($plainPassword) {
                    $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
                    $user->setUserPwd($hashedPassword);

                    // Send welcome email to user-provided email
                    try {
                        $email = (new TemplatedEmail())
                            ->from('amorrions@gmail.com')
                            ->to($user->getUserEmail())
                            ->subject('Welcome to SPIN! Your Account Details')
                            ->htmlTemplate('emails/registration.html.twig')
                            ->context([
                                'user' => $user,
                                'plainPassword' => $plainPassword,
                                'loginUrl' => $this->generateUrl('app_login', [], true),
                            ]);
                        $this->mailer->send($email);
                        $this->logger->info('Welcome email sent successfully to user-provided email', [
                            'to' => $user->getUserEmail(),
                            'from' => 'amorrions@gmail.com',
                            'subject' => 'Welcome to SPIN! Your Account Details',
                            'template' => 'emails/registration.html.twig',
                            'mailer_dsn' => getenv('MAILER_DSN'),
                        ]);
                        $this->addFlash('success', 'User created and welcome email sent to ' . $user->getUserEmail() . '. Please check your inbox or spam folder.');
                    } catch (TransportExceptionInterface $e) {
                        $this->logger->error('Failed to send welcome email to user-provided email', [
                            'email' => $user->getUserEmail(),
                            'from' => 'amorrions@gmail.com',
                            'error' => $e->getMessage(),
                            'code' => $e->getCode(),
                            'file' => $e->getFile(),
                            'line' => $e->getLine(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                        $this->addFlash('warning', 'User created, but failed to send email to ' . $user->getUserEmail() . ': ' . $e->getMessage());
                    } catch (\Exception $e) {
                        $this->logger->error('Unexpected error sending email to user-provided email', [
                            'email' => $user->getUserEmail(),
                            'from' => 'amorrions@gmail.com',
                            'error' => $e->getMessage(),
                            'code' => $e->getCode(),
                            'file' => $e->getFile(),
                            'line' => $e->getLine(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                        $this->addFlash('warning', 'User created, but an unexpected error occurred: ' . $e->getMessage());
                    }
                } else {
                    $this->logger->warning('No password provided in user creation form', [
                        'email' => $user->getUserEmail(),
                    ]);
                    $this->addFlash('error', 'Password is required.');
                    return $this->render($context === 'back' ? 'user/new_back.html.twig' : 'user/new.html.twig', [
                        'user' => $user,
                        'form' => $form->createView(),
                        'role' => $role,
                        'context' => $context,
                    ]);
                }

                $user->setUserRole($role);
                try {
                    $entityManager->persist($user);
                    $entityManager->flush();
                    $this->logger->info('User created successfully', ['email' => $user->getUserEmail(), 'role' => $role]);
                    return $this->redirectToRoute('app_user_index', ['context' => $context, 'role' => $role]);
                } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
                    $this->logger->error('Unique constraint violation in user creation', [
                        'email' => $user->getUserEmail(),
                        'error' => $e->getMessage(),
                    ]);
                    $this->addFlash('error', 'This email is already in use.');
                } catch (\Exception $e) {
                    $this->logger->error('Unexpected error in user creation', [
                        'email' => $user->getUserEmail(),
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    $this->addFlash('error', 'An error occurred while creating the user.');
                }
            } else {
                $this->addFlash('error', 'Please correct the errors in the form.');
            }
        }

        $template = $context === 'back' ? 'user/new_back.html.twig' : 'user/new.html.twig';
        return $this->render($template, [
            'user' => $user,
            'form' => $form->createView(),
            'role' => $role,
            'context' => $context,
        ]);
    }

    #[Route('/{context}/{role}', name: 'app_user_index', methods: ['GET'], defaults: ['context' => 'front', 'role' => 'athlete'], requirements: ['context' => 'front|back', 'role' => 'athlete|coach|med_staff'])]
    public function index(string $context, string $role, EntityManagerInterface $em, Request $request): Response
    {
        $context = strtolower(trim($context));
        $role = strtolower(trim($role));

        $validFrontRoles = ['athlete'];
        $validBackRoles = ['coach', 'med_staff'];

        if ($context === 'front' && in_array($role, $validFrontRoles, true)) {
            if (!$this->isGranted('ROLE_COACH') && !$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_MED_STAFF')) {
                $this->logger->error('Access denied: User lacks required roles for front office', [
                    'context' => $context,
                    'role' => $role,
                    'user' => $this->getUser() ? $this->getUser()->getUserIdentifier() : 'anonymous',
                ]);
                throw $this->createAccessDeniedException('Only coaches or admins can manage athletes in the front office.');
            }
        } elseif ($context === 'back' && in_array($role, $validBackRoles, true)) {
            if (!$this->isGranted('ROLE_ADMIN')) {
                $this->logger->error('Access denied: User lacks admin role for back office', [
                    'context' => $context,
                    'role' => $role,
                    'user' => $this->getUser() ? $this->getUser()->getUserIdentifier() : 'anonymous',
                ]);
                throw $this->createAccessDeniedException('Only admins can manage coaches or medical staff in the back office.');
            }
        } else {
            $this->logger->error('Invalid context or role', [
                'context' => $context,
                'role' => $role,
            ]);
            throw $this->createAccessDeniedException('Invalid context or role: context=' . $context . ', role=' . $role);
        }

        // Get search and sort parameters
        $searchTerm = trim($request->query->get('search', ''));
        $sortBy = $request->query->get('sort', 'id');
        $sortDir = strtoupper($request->query->get('dir', 'ASC')) === 'DESC' ? 'DESC' : 'ASC';

        // Validate sort column
        $allowedSortColumns = ['id', 'user_fname', 'user_lname', 'user_email'];
        if (!in_array($sortBy, $allowedSortColumns, true)) {
            $sortBy = 'id';
        }

        // Build query
        $queryBuilder = $em->getRepository(User::class)->createQueryBuilder('u')
            ->where('u.user_role = :role')
            ->setParameter('role', $role)
            ->orderBy('u.' . $sortBy, $sortDir);

        if ($searchTerm) {
            $queryBuilder->andWhere('LOWER(u.user_fname) LIKE :search OR LOWER(u.user_lname) LIKE :search')
                ->setParameter('search', '%' . strtolower($searchTerm) . '%');
        }

        try {
            $users = $queryBuilder->getQuery()->getResult();
        } catch (\Exception $e) {
            $this->logger->error('Database query failed', [
                'exception' => $e->getMessage(),
                'query' => $queryBuilder->getQuery()->getSQL(),
                'parameters' => ['role' => $role, 'search' => $searchTerm],
            ]);
            throw new \Exception('Failed to load users: ' . $e->getMessage());
        }

        // Handle AJAX request
        if ($request->headers->get('X-Requested-With') === 'XMLHttpRequest' || $request->query->get('ajax') === '1') {
            $this->logger->debug('AJAX request detected', [
                'headers' => $request->headers->all(),
                'query' => $request->query->all(),
            ]);
            $userData = array_map(function ($user) use ($role, $context) {
                $data = [
                    'id' => $user->getId(),
                    'userFname' => $user->getUserFname(),
                    'userLname' => $user->getUserLname(),
                    'userEmail' => $user->getUserEmail(),
                    'userNbr' => $user->getUserNbr() ?: 'N/A',
                    'showUrl' => $this->generateUrl('app_user_show', ['context' => $context, 'id' => $user->getId(), 'role' => $user->getUserRole()]),
                    'editUrl' => $this->generateUrl('app_user_edit', ['context' => $context, 'id' => $user->getId()]),
                    'deleteUrl' => $this->generateUrl('app_user_delete', ['context' => $context, 'id' => $user->getId()]),
                    'csrfToken' => $this->csrfTokenManager->getToken('delete' . $user->getId())->getValue(),
                ];
                if ($role === 'athlete') {
                    $data['athleteDoB'] = $user->getAthleteDoB() ? $user->getAthleteDoB()->format('Y-m-d') : 'N/A';
                    $data['athleteGender'] = $user->getAthleteGender() ?: 'N/A';
                    $data['athleteHeight'] = $user->getAthleteHeight() ?: 'N/A';
                    $data['athleteWeight'] = $user->getAthleteWeight() ?: 'N/A';
                    $data['isInjured'] = $user->getIsInjured() ?? false;
                } elseif ($role === 'coach') {
                    $data['nbTeams'] = $user->getNbTeams() ?: 'N/A';
                } elseif ($role === 'med_staff') {
                    $data['medSpecialty'] = $user->getMedSpecialty() ?: 'N/A';
                }
                return $data;
            }, $users);

            return new JsonResponse([
                'users' => $userData,
                'role' => $role,
                'context' => $context,
                'searchTerm' => $searchTerm,
                'sortBy' => $sortBy,
                'sortDir' => $sortDir,
            ]);
        }

        $this->logger->debug('Rendering full page', [
            'context' => $context,
            'role' => $role,
            'user_count' => count($users),
        ]);

        return $this->render('user/index.html.twig', [
            'users' => $users,
            'role' => $role,
            'context' => $context,
            'searchTerm' => $searchTerm,
            'sortBy' => $sortBy,
            'sortDir' => $sortDir,
        ]);
    }

    #[Route('/{context}/{id}/{role}', name: 'app_user_show', methods: ['GET'], defaults: ['context' => 'front', 'role' => null], requirements: ['id' => '\d+', 'context' => 'front|back', 'role' => 'athlete|coach|med_staff'])]
    public function show(string $context, User $user, ?string $role = null, Request $request): Response
    {
        $userRole = strtolower($user->getUserRole());
        $context = strtolower(trim($context));
        $validRoles = ['athlete', 'coach', 'med_staff'];
    
        // Debug: Log route and parameters
        dump([
            'action' => 'show',
            'path' => $request->getPathInfo(),
            'context' => $context,
            'id' => $user->getId(),
            'role' => $role,
            'user_role' => $userRole,
        ]);
    
        // Use the passed role if valid, otherwise fall back to user role
        $effectiveRole = $role && in_array(strtolower(trim($role ?? '')), $validRoles, true) ? strtolower(trim($role)) : $userRole;
    
        // Ensure effectiveRole matches userRole
        if ($effectiveRole !== $userRole) {
            throw $this->createAccessDeniedException('Role mismatch: provided role (' . $effectiveRole . ') does not match user role (' . $userRole . ') for user ID: ' . $user->getId());
        }
    
        if ($context === 'front' && $effectiveRole === 'athlete') {
            if (!$this->isGranted('ROLE_COACH') && !$this->isGranted('ROLE_ADMIN')) {
                throw $this->createAccessDeniedException('Only coaches or admins can view athletes in the front office.');
            }
        } elseif ($context === 'back' && in_array($effectiveRole, ['coach', 'med_staff'], true)) {
            if (!$this->isGranted('ROLE_ADMIN')) {
                throw $this->createAccessDeniedException('Only admins can view coaches or medical staff in the back office.');
            }
        } else {
            throw $this->createAccessDeniedException('Invalid context (' . $context . ') or effective role (' . $effectiveRole . ') for user ID: ' . $user->getId());
        }
    
        $template = $context === 'back' ? 'user/show_back.html.twig' : 'user/show.html.twig';
        return $this->render($template, [
            'user' => $user,
            'role' => $effectiveRole,
            'context' => $context,
        ]);
    }

    #[Route('/{context}/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'], defaults: ['context' => 'front'], requirements: ['id' => '\d+', 'context' => 'front|back'])]
    public function edit(string $context, Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $userRole = strtolower($user->getUserRole());
        $validFrontRoles = ['athlete'];
        $validBackRoles = ['coach', 'med_staff'];

        // Debug: Log route and parameters
        dump([
            'action' => 'edit',
            'path' => $request->getPathInfo(),
            'context' => $context,
            'id' => $user->getId(),
            'user_role' => $userRole,
            'method' => $request->getMethod(),
        ]);

        if ($context === 'front' && in_array($userRole, $validFrontRoles, true)) {
            if (!$this->isGranted('ROLE_COACH') && !$this->isGranted('ROLE_ADMIN')) {
                throw $this->createAccessDeniedException('Only coaches or admins can edit athletes in the front office.');
            }
        } elseif ($context === 'back' && in_array($userRole, $validBackRoles, true)) {
            if (!$this->isGranted('ROLE_ADMIN')) {
                throw $this->createAccessDeniedException('Only admins can edit coaches or medical staff in the back office.');
            }
        } else {
            throw $this->createAccessDeniedException('Invalid context or user role: context=' . $context . ', role=' . $userRole);
        }

        $form = $this->createForm(UserType::class, $user, ['is_new' => false, 'role' => $userRole]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            dump([
                'form_submitted' => true,
                'form_valid' => $form->isValid(),
                'form_errors' => $form->getErrors(true),
                'form_data' => $form->getData(),
            ]);
            if ($form->isValid()) {
                $plainPassword = $form->get('user_pwd')->getData();
                if ($plainPassword) {
                    $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
                    $user->setUserPwd($hashedPassword);
                }
                $entityManager->flush();

                return $this->redirectToRoute('app_user_index', ['context' => $context, 'role' => $userRole]);
            }
        }

        $template = $context === 'back' ? 'user/edit_back.html.twig' : 'user/edit.html.twig';
        return $this->render($template, [
            'user' => $user,
            'form' => $form->createView(),
            'role' => $userRole,
            'context' => $context,
        ]);
    }

    #[Route('/{context}/{id}', name: 'app_user_delete', methods: ['POST'], defaults: ['context' => 'front'])]
    public function delete(string $context, Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($context === 'front' && $user->getUserRole() === 'athlete') {
            if (!$this->isGranted('ROLE_COACH') && !$this->isGranted('ROLE_ADMIN')) {
                throw $this->createAccessDeniedException('Only coaches or admins can delete athletes in the front office.');
            }
        } elseif ($context === 'back' && ($user->getUserRole() === 'coach' || $user->getUserRole() === 'med_staff')) {
            if (!$this->isGranted('ROLE_ADMIN')) {
                throw $this->createAccessDeniedException('Only admins can delete coaches or medical staff in the back office.');
            }
        } else {
            throw $this->createAccessDeniedException('Invalid context or user role.');
        }

        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_index', ['context' => $context, 'role' => $user->getUserRole()], Response::HTTP_SEE_OTHER);
    }
}