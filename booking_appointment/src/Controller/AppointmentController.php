<?php

namespace App\Controller;

use App\Entity\Appointment;
use App\Entity\Service;
use App\Enum\AppointmentStatus;
use App\Enum\RoleEnum;
use App\Message\NotificationMessage;
use App\Repository\AppointmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/appointments')]
final class AppointmentController extends AbstractController
{
    public function __construct(
        private AppointmentRepository $appointmentRepository,
        private Security $security,
        ){}

    #[Route('', name: 'appointments_add', methods: ['POST'])]
    public function add(
        Request $request,
        EntityManagerInterface $em,
        MessageBusInterface $bus
        ): JsonResponse
    {
        /** @var \App\Entity\User */
        $user = $this->security->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Unauthorized'], 401);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['service_id'], $data['start_at'])) {
            return new JsonResponse(['error' => 'Invalid body'], 400);
        }

        $service = $em->getRepository(Service::class)->find($data['service_id']);
        if (!$service) {
            return new JsonResponse(['error' => 'Service not found'], 404);
        }

        $startAt = \DateTime::createFromFormat('H:i:s', $data['start_at']);
        $endAt = clone $startAt;
        $endAt->modify('+' . $service->getDurationInMinutes() . ' minutes');

        //TODO add query for uniq time appointment


        $appointment = new Appointment();
        $appointment->setCustomer($user);
        $appointment->setService($service);
        $appointment->setStartAt($startAt);
        $appointment->setEndAt($endAt);
        $appointment->setStatus(AppointmentStatus::PENDING );

        $em->persist($appointment);
        $em->flush();

        // send notification

        // end

        return new JsonResponse(['message' => 'Appointment created successfully'], 201);
    }

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
