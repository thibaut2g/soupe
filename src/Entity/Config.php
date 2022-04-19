<?php

namespace App\Entity;

use App\Repository\ConfigRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ConfigRepository::class)
 */
class Config
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isSubscriptionOpen;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $maxParticipantNumber;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $messageContent;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $messageColor;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIsSubscriptionOpen(): ?bool
    {
        return $this->isSubscriptionOpen;
    }

    public function setIsSubscriptionOpen(?bool $isSubscriptionOpen): self
    {
        $this->isSubscriptionOpen = $isSubscriptionOpen;

        return $this;
    }

    public function getMaxParticipantNumber(): ?int
    {
        return $this->maxParticipantNumber;
    }

    public function setMaxParticipantNumber(?int $maxParticipantNumber): self
    {
        $this->maxParticipantNumber = $maxParticipantNumber;

        return $this;
    }

    public function getMessageContent(): ?string
    {
        return $this->messageContent;
    }

    public function setMessageContent(?string $messageContent): self
    {
        $this->messageContent = $messageContent;

        return $this;
    }

    public function getMessageColor(): ?string
    {
        return $this->messageColor;
    }

    public function setMessageColor(?string $messageColor): self
    {
        $this->messageColor = $messageColor;

        return $this;
    }
}
