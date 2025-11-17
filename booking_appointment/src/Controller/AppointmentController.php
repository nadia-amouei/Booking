<?php

namespace App\Controller;

use App\Entity\Appointment;
use App\Entity\Service;
use App\Enum\AppointmentStatus;
use App\Enum\RoleEnum;
use App\Repository\AppointmentRepository;
use Doctrine\Common\Collections\ArrayCollection;
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
        private Security $security
        ){}

    #[Route('', name: 'appointments_add', methods: ['POST'])]
    public function add(Request $request, EntityManagerInterface $em): JsonResponse
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

        $startAt = new \DateTime($data['start_at']);
        $endAt = (clone $startAt)->modify("+{$service->getDurationInMinutes()} minutes");

        // check conflict
        $provider = $service->getProvider();

        $qb = $em->createQueryBuilder();
        $qb->select('a')
        ->from(Appointment::class, 'a')
        ->join('a.service', 's')
        ->where('s.provider = :provider')
        ->andWhere('a.status != :canceled')
        ->andWhere('(a.startAt < :endAt AND a.endAt > :startAt)')
        ->setParameters(new ArrayCollection([
            'provider'=> $provider,
            'startAt'=> $startAt,
            'endAt'=> $endAt,
            'canceled'=> AppointmentStatus::CANCELED,
        ]));

        $conflicts = $qb->getQuery()->getResult();

        if (count($conflicts) > 0) {
            return new JsonResponse([
                'error' => 'Time conflict: provider already has appointment in this time slot.'
            ], 409);
        }

        $appointment = new Appointment();
        $appointment->setCustomer($user);
        $appointment->setService($service);
        $appointment->setStartAt($startAt);
        $appointment->setEndAt($endAt);
        $appointment->setStatus(AppointmentStatus::PENDING );

        $em->persist($appointment);
        $em->flush();

        // send notification
        $message = [
            'type' => 'appointment_created',
            'appointmentId' => $appointment->getId(),
            'customerEmail' => $appointment->getCustomer()->getEmail(),
        ];

        $producer = $this->container->get('enqueue.default_producer');
        $producer->send('notifications', json_encode($message));
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
