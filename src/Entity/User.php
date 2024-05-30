<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    private ?string $username = null;

    #[ORM\Column(length: 255)]
    private ?string $fullname = null;

    #[ORM\Column(length: 255)]
    private ?string $phone = null;

    #[ORM\Column(length: 255)]
    private ?string $country = null;

    #[ORM\ManyToOne(targetEntity: self::class)]
    private ?self $ref = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(length: 255)]
    private ?string $visiblepassword = null;

    #[ORM\Column]
    private ?float $balance = null;

    #[ORM\Column]
    private ?float $profit = null;

    #[ORM\Column]
    private ?float $bonus = null;

    #[ORM\Column]
    private ?float $totaldeposit = null;

    #[ORM\Column]
    private ?float $totalwithdrawal = null;

    #[ORM\OneToMany(targetEntity: Notification::class, mappedBy: 'user')]
    private Collection $notifications;

    #[ORM\OneToMany(targetEntity: Transaction::class, mappedBy: 'user')]
    private Collection $transactions;

    #[ORM\OneToMany(targetEntity: Plan::class, mappedBy: 'user')]
    private Collection $plans;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $withdrawalerrormessage = null;

    #[ORM\OneToOne(mappedBy: 'user', cascade: ['persist', 'remove'])]
    private ?Kyc $kyc = null;

    public function __construct()
    {
        
        $this->notifications = new ArrayCollection();
        $this->transactions = new ArrayCollection();
        $this->plans = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getFullname(): ?string
    {
        return $this->fullname;
    }

    public function setFullname(string $fullname): static
    {
        $this->fullname = $fullname;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): static
    {
        $this->country = $country;

        return $this;
    }

    public function getRef(): ?self
    {
        return $this->ref;
    }

    public function setRef(?self $ref): static
    {
        $this->ref = $ref;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getVisiblepassword(): ?string
    {
        return $this->visiblepassword;
    }

    public function setVisiblepassword(string $visiblepassword): static
    {
        $this->visiblepassword = $visiblepassword;

        return $this;
    }
    public function getBalance(): ?float
    {
        return $this->balance;
    }

    public function setBalance(float $balance): static
    {
        $this->balance = $balance;

        return $this;
    }

    public function getProfit(): ?float
    {
        return $this->profit;
    }

    public function setProfit(float $profit): static
    {
        $this->profit = $profit;

        return $this;
    }

    public function getBonus(): ?float
    {
        return $this->bonus;
    }

    public function setBonus(float $bonus): static
    {
        $this->bonus = $bonus;

        return $this;
    }

    public function getTotaldeposit(): ?float
    {
        return $this->totaldeposit;
    }

    public function setTotaldeposit(float $totaldeposit): static
    {
        $this->totaldeposit = $totaldeposit;

        return $this;
    }

    public function getTotalwithdrawal(): ?float
    {
        return $this->totalwithdrawal;
    }

    public function setTotalwithdrawal(float $totalwithdrawal): static
    {
        $this->totalwithdrawal = $totalwithdrawal;

        return $this;
    }
     /**
     * @return Collection<int, Notification>
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(Notification $notification): static
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications->add($notification);
            $notification->setUser($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): static
    {
        if ($this->notifications->removeElement($notification)) {
            // set the owning side to null (unless already changed)
            if ($notification->getUser() === $this) {
                $notification->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Transaction>
     */
    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    public function addTransaction(Transaction $transaction): static
    {
        if (!$this->transactions->contains($transaction)) {
            $this->transactions->add($transaction);
            $transaction->setUser($this);
        }

        return $this;
    }

    public function removeTransaction(Transaction $transaction): static
    {
        if ($this->transactions->removeElement($transaction)) {
            // set the owning side to null (unless already changed)
            if ($transaction->getUser() === $this) {
                $transaction->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Plan>
     */
    public function getPlans(): Collection
    {
        return $this->plans;
    }

    public function addPlan(Plan $plan): static
    {
        if (!$this->plans->contains($plan)) {
            $this->plans->add($plan);
            $plan->setUser($this);
        }

        return $this;
    }

    public function removePlan(Plan $plan): static
    {
        if ($this->plans->removeElement($plan)) {
            // set the owning side to null (unless already changed)
            if ($plan->getUser() === $this) {
                $plan->setUser(null);
            }
        }

        return $this;
    }

    public function getWithdrawalerrormessage(): ?string
    {
        return $this->withdrawalerrormessage;
    }

    public function setWithdrawalerrormessage(?string $withdrawalerrormessage): static
    {
        $this->withdrawalerrormessage = $withdrawalerrormessage;

        return $this;
    }

    public function getKyc(): ?Kyc
    {
        return $this->kyc;
    }

    public function setKyc(?Kyc $kyc): static
    {
        // unset the owning side of the relation if necessary
        if ($kyc === null && $this->kyc !== null) {
            $this->kyc->setUser(null);
        }

        // set the owning side of the relation if necessary
        if ($kyc !== null && $kyc->getUser() !== $this) {
            $kyc->setUser($this);
        }

        $this->kyc = $kyc;

        return $this;
    }
}
