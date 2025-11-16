<?php

namespace App\Controller;

use App\Entity\Appointment;
use App\Enum\RoleEnum;
use App\Repository\AppointmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class AppointmentController extends AbstractController
{
    public function __construct(
        private AppointmentRepository $appointmentRepository,
        ){}


    #[Route('', name: 'appointments_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $this->denyAccessUnlessGranted(RoleEnum::ADMIN->value);

        $appointments = $this->appointmentRepository->findAll();

        $data = array_map(fn(Appointment $appointment) => [
            'id' => $appointment->getId(),
            'status' => $appointment->getStatus(),
            'start_at' => $appointment->getStartAt(),
            'end_at' => $appointment->getEndAt(),
            'created_at' => $appointment->getCreatedAt(),
            'updated_at' => $appointment->getUpdatedAt(),
        ], $appointments);

        return $this->json($data);

    }

    #[Route('/{id}', name: 'appointments_show', methods: ['GET'])]
    public function show(Appointment $appointment): JsonResponse
    {
        if (
            !$this->isGranted(RoleEnum::ADMIN->value)
        ) {
            return $this->json(['error' => 'Access denied'], 403);
        }

        return $this->json([
            'id' => $appointment->getId(),
            'status' => $appointment->getStatus(),
            'start_at' => $appointment->getStartAt(),
            'end_at' => $appointment->getEndAt(),
            'created_at' => $appointment->getCreatedAt(),
            'updated_at' => $appointment->getUpdatedAt(),
        ]);
    }

    #[Route('/{id}', name: 'appointments_update', methods: ['PUT'])]
    public function update(
        Request $request,
        Appointment $appointment,
        EntityManagerInterface $entityManager,
        ): JsonResponse
    {

        if (
            !$this->isGranted(RoleEnum::ADMIN->value)
        ) {
            return $this->json(['error' => 'Access denied'], 403);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['status'])) {
            $appointment->setStatus($data['status']);
        }

        if (isset($data['start_at'])) {
            $appointment->setStartAt($data['start_at']);
        }

        if (isset($data['end_at'])) {
            $appointment->setEndAt($data['end_at']);
        }

        $appointment->setUpdatedAt(new \DateTime());

        $entityManager->flush();


        return $this->json([
            'id' => $appointment->getId(),
            'status' => $appointment->getStatus(),
            'start_at' => $appointment->getStartAt(),
            'end_at' => $appointment->getEndAt(),
            'created_at' => $appointment->getCreatedAt(),
            'updated_at' => $appointment->getUpdatedAt(),
        ]);

    }

    #[Route('/{id}', name: 'appointments_delete', methods: ['DELETE'])]
    public function delete(Appointment $appointment, EntityManagerInterface $entityManager): JsonResponse
    {
        $this->denyAccessUnlessGranted(RoleEnum::ADMIN->value);

        $entityManager->remove($appointment);
        $entityManager->flush();


        return $this->json(['status' => 'Appointment deleted']);
    }
}
