<?php declare(strict_types=1);

namespace App\Entity;

use App\Repository\PlayerRepository;
use Doctrine\ORM\Mapping as ORM;
use SteamID\SteamID;
use Symfony\Component\Serializer\Annotation as Serializer;

#[ORM\Entity(repositoryClass: PlayerRepository::class)]
class Player
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Serializer\Groups(['player'])]
    private int $id;

    #[ORM\Column(length: 255, nullable: true)]
    #[Serializer\Groups(['player'])]
    private ?string $description = null;

    #[ORM\Column(type: 'steam_id', length: 255)]
    #[Serializer\Groups(['player'])]
    private SteamID $steamId;

    #[ORM\Column(length: 255)]
    #[Serializer\Groups(['player'])]
    private string $miniProfileId;

    #[ORM\Column(nullable: true)]
    #[Serializer\Groups(['player'])]
    private ?\DateTimeImmutable $lastSeenAt = null;

    #[ORM\Column]
    #[Serializer\Groups(['player'])]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'steam_id')]
    private SteamID $belongsTo;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getSteamId(): SteamID
    {
        return $this->steamId;
    }

    public function setSteamId(SteamID $steamId): self
    {
        $this->steamId = $steamId;

        return $this;
    }

    public function getMiniProfileId(): string
    {
        return $this->miniProfileId;
    }

    public function setMiniProfileId(string $miniProfileId): self
    {
        $this->miniProfileId = $miniProfileId;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getLastSeenAt(): ?\DateTimeImmutable
    {
        return $this->lastSeenAt;
    }

    public function setLastSeenAt(?\DateTimeImmutable $lastSeenAt): self
    {
        $this->lastSeenAt = $lastSeenAt;

        return $this;
    }

    public function getBelongsTo()
    {
        return $this->belongsTo;
    }

    public function setBelongsTo(SteamID $belongsTo): self
    {
        $this->belongsTo = $belongsTo;

        return $this;
    }
}
