<?php

namespace App\Entity;

use App\Repository\CongesRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=CongesRepository::class)
 */
class Conges
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="date")
     */
    public $date_demande;

    /**
     * @ORM\Column(type="date")
     * @Assert\GreaterThanOrEqual(value = "today", message = "Veuillez ne pas insérer une date avant aujourd'hui!")
     */
    public $date_depart;

    /**
     * @ORM\Column(type="date")
     * @Assert\GreaterThanOrEqual(propertyPath = "date_depart", message = "La date de retour doit être ultérieur au date de départ!")
     */
    public $date_retour;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $motif;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $etat;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="conges")
     * @ORM\JoinColumn(nullable=false)
     */
    private $users;

    /**
     * @ORM\Column(type="string", length=255)
     */
    public $nb_jours;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $etat_badge;


    public function __toString()
    {
        return(string) $this->users;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateDemande(): ?\DateTimeInterface
    {
        return $this->date_demande;
    }

    public function setDateDemande(\DateTimeInterface $date_demande): self
    {
        $this->date_demande = $date_demande;

        return $this;
    }

    public function getDateDepart(): ?\DateTimeInterface
    {
        return $this->date_depart;
    }

    public function setDateDepart(\DateTimeInterface $date_depart): self
    {
        $this->date_depart = $date_depart;

        return $this;
    }

    public function getDateRetour(): ?\DateTimeInterface
    {
        return $this->date_retour;
    }

    public function setDateRetour(\DateTimeInterface $date_retour): self
    {
        $this->date_retour = $date_retour;

        return $this;
    }

    public function getMotif(): ?string
    {
        return $this->motif;
    }

    public function setMotif(string $motif): self
    {
        $this->motif = $motif;

        return $this;
    }

    public function getEtat(): ?string
    {
        return $this->etat;
    }

    public function setEtat(string $etat): self
    {
        $this->etat = $etat;

        return $this;
    }

    public function getUsers(): ?user
    {
        return $this->users;
    }

    public function setUsers(?user $users): self
    {
        $this->users = $users;

        return $this;
    }

    public function getNbJours(): ?string
    {
        return $this->nb_jours;
    }

    public function setNbJours(string $nb_jours): self
    {
        $this->nb_jours = $nb_jours;

        return $this;
    }

    public function getEtatBadge(): ?string
    {
        return $this->etat_badge;
    }

    public function setEtatBadge(string $etat_badge): self
    {
        $this->etat_badge = $etat_badge;

        return $this;
    }
}
