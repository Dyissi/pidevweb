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

    #[Route('/{context}/{role}', name: 'app_user_index', methods: ['GET'], defaults: ['context' => 'front', 'role' => 'athlete'])]
    public function index(string $context, string $role, EntityManagerInterface $em): Response
    {
        $role = strtolower($role); // Normalize role
        $coachRoles = ['coach', 'coaches'];
        $medStaffRoles = ['med_staff', 'medical_staff', 'medical staff'];

        if ($context === 'front' && $role === 'athlete') {
            if (!$this->isGranted('ROLE_COACH') && !$this->isGranted('ROLE_ADMIN')) {
                throw $this->createAccessDeniedException('Only coaches or admins can manage athletes in the front office.');
            }
        } elseif ($context === 'back' && (in_array($role, $coachRoles) || in_array($role, $medStaffRoles))) {
            if (!$this->isGranted('ROLE_ADMIN')) {
                throw $this->createAccessDeniedException('Only admins can manage coaches or medical staff in the back office.');
            }
        } else {
            throw $this->createAccessDeniedException('Invalid context or role: ' . $role);
        }

        $users = $em->getRepository(User::class)->findBy(['user_role' => $role]);

        // Validate that all users match the expected role
        foreach ($users as $user) {
            $userRole = strtolower($user->getUserRole());
            if ($context === 'back' && !in_array($userRole, $coachRoles) && !in_array($userRole, $medStaffRoles)) {
                throw new \RuntimeException('User ID ' . $user->getId() . ' has role ' . $userRole . ', which does not match expected roles for back context.');
            }
        }

        $template = $context === 'back' ? 'user/index_back.html.twig' : 'user/index.html.twig';
        return $this->render($template, [
            'users' => $users,
            'role' => $role,
            'context' => $context,
        ]);
    }

    #[Route('/new/{context}/{role}', name: 'app_user_new', methods: ['GET', 'POST'], defaults: ['context' => 'front', 'role' => 'athlete'])]
    public function new(string $context, string $role, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($context === 'front' && $role === 'athlete') {
            if (!$this->isGranted('ROLE_COACH') && !$this->isGranted('ROLE_ADMIN')) {
                throw $this->createAccessDeniedException('Only coaches or admins can create athletes in the front office.');
            }
        } elseif ($context === 'back' && ($role === 'coach' || $role === 'med_staff')) {
            if (!$this->isGranted('ROLE_ADMIN')) {
                throw $this->createAccessDeniedException('Only admins can create coaches or medical staff in the back office.');
            }
        } else {
            throw $this->createAccessDeniedException('Invalid context or role.');
        }

        $user = new User();
        $form = $this->createForm(UserType::class, $user, ['is_new' => true, 'role' => $role]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('user_pwd')->getData();
            if ($plainPassword) {
                $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
                $user->setUserPwd($hashedPassword);
            }

            $user->setUserRole($role);
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_user_index', ['context' => $context, 'role' => $role]);
        }

        $template = $context === 'back' ? 'user/new_back.html.twig' : 'user/new.html.twig';
        return $this->render($template, [
            'user' => $user,
            'form' => $form,
            'role' => $role,
            'context' => $context,
        ]);
    }

    #[Route('/{context}/{id}/{role}', name: 'app_user_show', methods: ['GET'], defaults: ['context' => 'front', 'role' => null], requirements: ['id' => '\d+'])]
    public function show(string $context, User $user, ?string $role = null): Response
    {
        $userRole = strtolower($user->getUserRole()); // Normalize role
        $coachRoles = ['coach', 'coaches'];
        $medStaffRoles = ['med_staff', 'medical_staff', 'medical staff'];

        // Use the passed role if available, otherwise fall back to user role
        $effectiveRole = $role ? strtolower($role) : $userRole;

        if ($context === 'front' && $effectiveRole === 'athlete') {
            if (!$this->isGranted('ROLE_COACH') && !$this->isGranted('ROLE_ADMIN')) {
                throw $this->createAccessDeniedException('Only coaches or admins can view athletes in the front office.');
            }
        } elseif ($context === 'back' && (in_array($effectiveRole, $coachRoles) || in_array($effectiveRole, $medStaffRoles))) {
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

    #[Route('/{context}/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'], defaults: ['context' => 'front'])]
    public function edit(string $context, Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($context === 'front' && $user->getUserRole() === 'athlete') {
            if (!$this->isGranted('ROLE_COACH') && !$this->isGranted('ROLE_ADMIN')) {
                throw $this->createAccessDeniedException('Only coaches or admins can edit athletes in the front office.');
            }
        } elseif ($context === 'back' && ($user->getUserRole() === 'coach' || $user->getUserRole() === 'med_staff')) {
            if (!$this->isGranted('ROLE_ADMIN')) {
                throw $this->createAccessDeniedException('Only admins can edit coaches or medical staff in the back office.');
            }
        } else {
            throw $this->createAccessDeniedException('Invalid context or user role.');
        }

        $form = $this->createForm(UserType::class, $user, ['is_new' => false, 'role' => $user->getUserRole()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('user_pwd')->getData();
            if ($plainPassword) {
                $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
                $user->setUserPwd($hashedPassword);
            }
            $entityManager->flush();

            return $this->redirectToRoute('app_user_index', ['context' => $context, 'role' => $user->getUserRole()]);
        }

        $template = $context === 'back' ? 'user/edit_back.html.twig' : 'user/edit.html.twig';
        return $this->render($template, [
            'user' => $user,
            'form' => $form,
            'role' => $user->getUserRole(),
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