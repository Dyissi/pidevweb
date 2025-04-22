<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/user')]
final class UserController extends AbstractController
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    #[Route('/new/{context}/{role}', name: 'app_user_new', methods: ['GET', 'POST'], defaults: ['context' => 'front', 'role' => 'athlete'], requirements: ['context' => 'front|back', 'role' => 'athlete|coach|med_staff'])]
    public function new(string $context, string $role, Request $request, EntityManagerInterface $entityManager): Response
    {
        $context = strtolower(trim($context));
        $role = strtolower(trim($role));

        // Debug: Log all relevant data
        dump([
            'action' => 'new',
            'path' => $request->getPathInfo(),
            'context' => $context,
            'role' => $role,
            'method' => $request->getMethod(),
            'query_params' => $request->query->all(),
            'form_data' => $request->request->all(),
        ]);

        $validFrontRoles = ['athlete'];
        $validBackRoles = ['coach', 'med_staff'];

        if ($context === 'front' && in_array($role, $validFrontRoles, true)) {
            if (!$this->isGranted('ROLE_COACH') && !$this->isGranted('ROLE_ADMIN')) {
                throw $this->createAccessDeniedException('Only coaches or admins can create athletes in the front office.');
            }
        } elseif ($context === 'back' && in_array($role, $validBackRoles, true)) {
            if (!$this->isGranted('ROLE_ADMIN')) {
                throw $this->createAccessDeniedException('Only admins can create coaches or medical staff in the back office.');
            }
        } else {
            throw $this->createAccessDeniedException('Invalid context or role: context=' . $context . ', role=' . $role);
        }

        $user = new User();
        $form = $this->createForm(UserType::class, $user, ['is_new' => true, 'role' => $role]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            dump([
                'form_submitted' => true,
                'form_valid' => $form->isValid(),
                'form_errors' => $form->getErrors(true, true),
                'form_data' => $form->getData(),
                'submitted_role' => $request->request->get('role'),
            ]);
            if ($form->isValid()) {
                $plainPassword = $form->get('user_pwd')->getData();
                if ($plainPassword) {
                    $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
                    $user->setUserPwd($hashedPassword);
                }

                $user->setUserRole($role);
                $entityManager->persist($user);
                $entityManager->flush();

                return $this->redirectToRoute('app_user_index', ['context' => $context, 'role' => $role]);
            } else {
                // Log form errors for debugging
                $errors = [];
                foreach ($form->getErrors(true) as $error) {
                    $errors[] = $error->getMessage();
                }
                dump(['form_validation_errors' => $errors]);
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

        // Debug: Log route and parameters
        dump([
            'action' => 'index',
            'path' => $request->getPathInfo(),
            'context' => $context,
            'role' => $role,
            'method' => $request->getMethod(),
        ]);

        $validFrontRoles = ['athlete'];
        $validBackRoles = ['coach', 'med_staff'];

        if ($context === 'front' && in_array($role, $validFrontRoles, true)) {
            if (!$this->isGranted('ROLE_COACH') && !$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_MED_STAFF')) {
                throw $this->createAccessDeniedException('Only coaches or admins can manage athletes in the front office.');
            }
        } elseif ($context === 'back' && in_array($role, $validBackRoles, true)) {
            if (!$this->isGranted('ROLE_ADMIN')) {
                throw $this->createAccessDeniedException('Only admins can manage coaches or medical staff in the back office.');
            }
        } else {
            throw $this->createAccessDeniedException('Invalid context or role: context=' . $context . ', role=' . $role);
        }

        $users = $em->getRepository(User::class)->findBy(['user_role' => $role]);

        $template = $context === 'back' ? 'user/index_back.html.twig' : 'user/index.html.twig';
        return $this->render($template, [
            'users' => $users,
            'role' => $role,
            'context' => $context,
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

        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_index', ['context' => $context, 'role' => $user->getUserRole()], Response::HTTP_SEE_OTHER);
    }
}