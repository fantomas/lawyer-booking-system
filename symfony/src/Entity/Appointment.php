<?php

namespace App\Entity;

use App\Repository\AppointmentRepository;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\BlameableInterface;
use Knp\DoctrineBehaviors\Model\Blameable\BlameableTrait;

/**
 * @ORM\Entity(repositoryClass=AppointmentRepository::class)
 * @ORM\HasLifecycleCallbacks
 */
class Appointment implements BlameableInterface
{
	use BlameableTrait;
	
	const STATUS_PENDING = 'pending';
	const STATUS_APPROVED = 'approved';
	const STATUS_REJECTED = 'rejected';
	const BUSINESS_HOURS = [9,10,11,13,14,15,16,17];
	
	/**
	 * @ORM\PrePersist
	 * @ORM\PreUpdate
	 */
	public function updatedTimestamps(): void
	{
		$this->setUpdatedAt(new \DateTime('now'));
		if ($this->getCreatedAt() === null) {
			$this->setCreatedAt(new \DateTime('now'));
		}
	}
	
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="date")
     */
    private $book_date;
	
	/**
	 * @ORM\Column(type="integer")
	 */
	private $book_hour;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="appointments")
     */
    private $created_by;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $lawyer;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updated_at;

    /**
	 * @ORM\Column(name="status", type="string", columnDefinition="enum('pending', 'approved', 'rejected')")
     */
    private $status;
    
    public function __construct()
	{
		$this->setCreatedAt(new \DateTime());
		$this->setStatus(self::STATUS_PENDING);
	}
	
	public function getId(): ?int
    {
        return $this->id;
    }

    public function getBookDate(): ?\DateTimeInterface
    {
        return $this->book_date;
    }

    public function setBookDate(\DateTimeInterface $book_date): self
    {
        $this->book_date = $book_date;

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->created_by;
    }

    public function getLawyer(): ?User
    {
        return $this->lawyer;
    }

    public function setLawyer(?User $lawyer): self
    {
        $this->lawyer = $lawyer;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeInterface $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
		if (!in_array($status, array(self::STATUS_PENDING, self::STATUS_APPROVED, self::STATUS_REJECTED))) {
			throw new \InvalidArgumentException("Invalid status");
		}
		
        $this->status = $status;

        return $this;
    }
	
	/**
	 * @return mixed
	 */
	public function getBookHour():?int
	{
		return $this->book_hour;
	}
	
	/**
	 * @param int $book_hour
	 *
	 * @return Appointment
	 */
	public function setBookHour(int $book_hour): self
	{
		if (!in_array($book_hour, self::BUSINESS_HOURS)) {
			throw new \InvalidArgumentException("Invalid hour");
		}
		
		$this->book_hour = $book_hour;
		
		return $this;
	}
}
