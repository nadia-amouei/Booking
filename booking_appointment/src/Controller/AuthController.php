<?php

namespace App\Controller;

use App\Entity\User;
use App\Enum\RoleEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class AuthController extends AbstractController
{

    #[Route('/test', name: 'test', methods: ['get'])]
    public function test(): JsonResponse
    {
        return $this->json(['working']);
    }

    #[Route('/api/register', name: 'register', methods: ['POST'])]
    public function register (
        Request $request,
        ValidatorInterface $validator,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
        ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $user = new User();
        $user->setEmail($data['email'] ?? null);
        $user->setPassword($data['password'] ?? null);
        $user->setRole(RoleEnum::USER);

        $errors = $validator->validate($user);
        if (count($errors) > 0) {

            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], 400);

        }


        $hashedPassword = $passwordHasher->hashPassword($user, plainPassword: $data['password']);
        $user->setPassword($hashedPassword);

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json([
            'message' => 'User registered successfully',
        ], 201);
    }

}
