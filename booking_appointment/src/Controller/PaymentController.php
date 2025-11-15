<?php

namespace App\Controller;

use App\Entity\Payment;
use App\Enum\RoleEnum;
use App\Repository\PaymentRepository;
use App\Repository\ServiceRepository;
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
            'id' => $service->getId(),
            'provider_id' => $service->getProviderId(),
            'name' => $service->getName(),
            'duration_minutes' => $service->getDurationInMinutes(),
            'status' => $service->getStatus(),
            'price' => $service->getPrice(),
            'created_at' => $service->getCreatedAt(),
            'updated_at' => $service->getUpdatedAt()
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
