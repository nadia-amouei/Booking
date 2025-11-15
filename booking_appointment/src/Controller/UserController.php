<?php

namespace App\Controller;

use App\Entity\User;
use App\Enum\RoleEnum;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/api/users')]
final class UserController extends AbstractController
{

    public function __construct(
        private UserRepository $userRepository,
        private Security $security
        ){}


    #[Route('', name: 'users_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $this->denyAccessUnlessGranted(RoleEnum::ADMIN->value);

        $users = $this->userRepository->findAll();

        $data = array_map(fn(User $user) => [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'role' => $user->getRole()->value,
        ], $users);

        return $this->json($data);

    }

    #[Route('/{id}', name: 'users_show', methods: ['GET'])]
    public function show(User $user): JsonResponse
    {
        $currentUser = $this->security->getUser();

        if (
            $currentUser !== $user &&
            !$this->isGranted(RoleEnum::ADMIN->value)
            ) {
            return $this->json(['error' => 'Access denied'], 403);
        }

        return $this->json([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'role' => $user->getRole()->value,
        ]);
    }

    #[Route('/{id}', name: 'users_update', methods: ['PUT'])]
    public function update(
        Request $request,
        User $user,
        EntityManagerInterface $entityManager,
        ): JsonResponse
    {
        $currentUser = $this->security->getUser();


        if (
            $currentUser !== $user &&
            !$this->isGranted(RoleEnum::ADMIN->value)
        ) {
            return $this->json(['error' => 'Access denied'], 403);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['email'])) {
            $user->setEmail($data['email']);
        }

        if (isset($data['role'])) {
            $user->setRole(RoleEnum::from($data['role']));
        }

        $user->setUpdatedAt(new \DateTime());

        $entityManager->flush();


        return $this->json([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'role' => $user->getRole()->value,
        ]);

    }

    #[Route('/{id}', name: 'users_delete', methods: ['DELETE'])]
    public function delete(User $user, EntityManagerInterface $entityManager): JsonResponse
    {
        $this->denyAccessUnlessGranted(RoleEnum::ADMIN->value);

        $entityManager->remove($user);
        $entityManager->flush();


        return $this->json(['status' => 'User deleted']);
    }
}
