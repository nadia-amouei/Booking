<?php

namespace App\Entity;

use App\Enum\AppointmentStatus;
use App\Repository\AppointmentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AppointmentRepository::class)]
class Appointment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'appointments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $customer_id = null;

    #[ORM\ManyToOne(inversedBy: 'appointments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Service $service_id = null;

    #[ORM\Column(type:  'string' , enumType: AppointmentStatus::class)]
    private AppointmentStatus $status;

    /**
     * @var Collection<int, Payment>
     */
    #[ORM\OneToMany(targetEntity: Payment::class, mappedBy: 'appointment_id')]
    private Collection $payments;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTime $start_at = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTime $end_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    private ?\DateTime $updated_at = null;

    public function __construct()
    {
        $this->payments = new ArrayCollection();

        $this->created_at = new \DateTimeImmutable();
        $this->updated_at = new \DateTime(); 
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCustomerId(): ?User
    {
        return $this->customer_id;
    }

    public function setCustomerId(?User $customer_id): static
    {
        $this->customer_id = $customer_id;

        return $this;
    }

    public function getServiceId(): ?Service
    {
        return $this->service_id;
    }

    public function setServiceId(?Service $service_id): static
    {
        $this->service_id = $service_id;

        return $this;
    }

    public function getStatus(): AppointmentStatus
    {
        return $this->status;
    }

    public function setStatus(AppointmentStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection<int, Payment>
     */
    public function getPayments(): Collection
    {
        return $this->payments;
    }

    public function addPayment(Payment $payment): static
    {
        if (!$this->payments->contains($payment)) {
            $this->payments->add($payment);
            $payment->setAppointmentId($this);
        }

        return $this;
    }

    public function removePayment(Payment $payment): static
    {
        if ($this->payments->removeElement($payment)) {
            // set the owning side to null (unless already changed)
            if ($payment->getAppointmentId() === $this) {
                $payment->setAppointmentId(null);
            }
        }

        return $this;
    }

    public function getStartAt(): ?\DateTime
    {
        return $this->start_at;
    }

    public function setStartAt(\DateTime $start_at): static
    {
        $this->start_at = $start_at;

        return $this;
    }

    public function getEndAt(): ?\DateTime
    {
        return $this->end_at;
    }

    public function setEndAt(\DateTime $end_at): static
    {
        $this->end_at = $end_at;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTime $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }
}
