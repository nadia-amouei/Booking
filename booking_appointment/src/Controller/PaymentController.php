<?php

namespace App\Controller;

use App\Entity\Appointment;
use App\Entity\Payment;
use App\Entity\User;
use App\Enum\PaymentStatus;
use App\Enum\RoleEnum;
use App\Repository\PaymentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class PaymentController extends AbstractController
{
    public function __construct(
            private PaymentRepository $paymentRepository,
            private Security $security
        ){}


    #[Route('', name: 'services_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $this->denyAccessUnlessGranted(RoleEnum::ADMIN->value);

        $payments = $this->paymentRepository->findAll();

        $data = array_map(fn(Payment $payment) => [
            'id' => $payment->getId(),
            'status' => $payment->getStatus(),
            'created_at' => $payment->getCreatedAt(),
            'updated_at' => $payment->getUpdatedAt(),

        ], $payments);

        return $this->json($data);

    }

    #[Route('', name: 'payments_add', methods: ['POST'])]
    public function add(Request $request, EntityManagerInterface $em): JsonResponse
    {
        /** @var \App\Entity\User */
        $user = $this->security->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Unauthorized'], 401);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['appointment_id'], $data['customer_id'])) {
            return new JsonResponse(['error' => 'Invalid body'], 400);
        }

        $appointment = $em->getRepository(Appointment::class)->find($data['appointment_id']);
        if (!$appointment) {
            return new JsonResponse(['error' => 'Appointment not found'], 404);
        }
        $customer = $em->getRepository(User::class)->find($data['customer_id']);
        if (!$customer) {
            return new JsonResponse(['error' => 'Customer not found'], 404);
        }


        $payment = new Payment();
        $payment->setCustomer($customer);
        $payment->setAppointment($appointment);
        $payment->setStatus(PaymentStatus::PENDING->value);

        $em->persist($payment);
        $em->flush();

        return new JsonResponse(['message' => 'Appointment created successfully'], 201);
    }

    #[Route('/{id}', name: 'services_show', methods: ['GET'])]
    public function show(Payment $payment): JsonResponse
    {

        if (
            !$this->isGranted(RoleEnum::ADMIN->value)
        ) {
            return $this->json(['error' => 'Access denied'], 403);
        }

        return $this->json([
            'id' => $payment->getId(),
            'status' => $payment->getStatus(),
            'created_at' => $payment->getCreatedAt(),
            'updated_at' => $payment->getUpdatedAt(),
        ]);
    }

    #[Route('/{id}', name: 'services_update', methods: ['PUT'])]
    public function update(
        Request $request,
        Payment $payment,
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
            $payment->setStatus($data['status']);
        }

        $payment->setUpdatedAt(new \DateTime());

        $entityManager->flush();


        return $this->json([
            'id' => $payment->getId(),
        ]);

    }

    #[Route('/{id}', name: 'services_delete', methods: ['DELETE'])]
    public function delete(Payment $payment, EntityManagerInterface $entityManager): JsonResponse
    {
        $this->denyAccessUnlessGranted(RoleEnum::ADMIN->value);

        $entityManager->remove($payment);
        $entityManager->flush();


        return $this->json(['status' => 'Payment deleted']);
    }
}
