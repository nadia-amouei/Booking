<?php

namespace App\Controller;

use App\Enum\RoleEnum;
use App\Entity\Service;
use App\Enum\ServiceStatus;
use App\Repository\ServiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/services')]
final class ServiceController extends AbstractController
{
    public function __construct (
        private ServiceRepository $serviceRepository,
        private Security $security,
        ){}

    #[Route('', name: 'service_add', methods: ['POST'])]
    public function add(Request $request, EntityManagerInterface $em): JsonResponse
    {

        if (
            !$this->isGranted(RoleEnum::ADMIN->value) &&
            !$this->isGranted(RoleEnum::PROVIDER->value)
        ) {
            return $this->json(['error' => 'Access denied'], 403);
        }

        /** @var \App\Entity\User $user **/
        $user = $this->security->getUser();

        if (!$user) {
            return new JsonResponse(['error' => 'Unauthorized'], 401);
        }


        $data = json_decode($request->getContent(), true);

        if (!isset($data['name'], $data['duration_minutes'], $data['price'])) {
            return new JsonResponse(['error' => 'Invalid body'], 400);
        }

        $service = new Service();
        $service->setName($data['name']);
        $service->setDurationInMinutes($data['duration_minutes']);
        $service->setPrice($data['price']);
        $service->setStatus(ServiceStatus::ACTIVE );
        $service->setProvider($user);

        $em->persist($service);
        $em->flush();

        return new JsonResponse(['message' => 'Service created successfully'], 201);
    }

    #[Route('', name: 'services_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $this->denyAccessUnlessGranted(RoleEnum::ADMIN->value);

        $services = $this->serviceRepository->findAll();

        $data = array_map(fn(Service $service) => [
            'id' => $service->getId(),
            'provider_id' => $service->getProvider(),
            'name' => $service->getName(),
            'duration_minutes' => $service->getDurationInMinutes(),
            'status' => $service->getStatus(),
            'price' => $service->getPrice(),
            'created_at' => $service->getUpdatedAt(),
            'updated_at' => $service->getCreatedAt()
        ], $services);

        return $this->json($data);

    }

    #[Route('/{id}', name: 'services_show', methods: ['GET'])]
    public function show(Service $service): JsonResponse
    {

        if (
            !$this->isGranted(RoleEnum::ADMIN->value)
        ) {
            return $this->json(['error' => 'Access denied'], 403);
        }

        return $this->json([
            'id' => $service->getId(),
            'provider_id' => $service->getProvider(),
            'name' => $service->getName(),
            'duration_minutes' => $service->getDurationInMinutes(),
            'status' => $service->getStatus(),
            'price' => $service->getPrice(),
            'created_at' => $service->getCreatedAt(),
            'updated_at' => $service->getUpdatedAt()
        ]);
    }

    #[Route('/{id}', name: 'services_update', methods: ['PUT'])]
    public function update(
        Request $request,
        Service $service,
        EntityManagerInterface $entityManager,
        ): JsonResponse
    {

        if (
            !$this->isGranted(RoleEnum::ADMIN->value)
        ) {
            return $this->json(['error' => 'Access denied'], 403);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['name'])) {
            $service->setName($data['name']);
        }

        if (isset($data['duration_minutes'])) {
            $service->setDurationInMinutes($data['duration_minutes']);
        }

        if (isset($data['status'])) {
            $service->setStatus($data['status']);
        }

        if (isset($data['price'])) {
            $service->setPrice($data['price']);
        }

        $service->setUpdatedAt(new \DateTime());

        $entityManager->flush();


        return $this->json([
            'id' => $service->getId(),
            'provider_id' => $service->getProvider(),
            'name' => $service->getName(),
            'duration_minutes' => $service->getDurationInMinutes(),
            'status' => $service->getStatus(),
            'price' => $service->getPrice(),
            'created_at' => $service->getCreatedAt(),
            'updated_at' => $service->getUpdatedAt()
        ]);

    }

    #[Route('/{id}', name: 'services_delete', methods: ['DELETE'])]
    public function delete(Service $service, EntityManagerInterface $entityManager): JsonResponse
    {
        $this->denyAccessUnlessGranted(RoleEnum::ADMIN->value);

        $entityManager->remove($service);
        $entityManager->flush();


        return $this->json(['status' => 'Service deleted']);
    }


}
