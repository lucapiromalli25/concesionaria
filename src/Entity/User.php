<?php

namespace App\Entity;

use App\Entity\Trait\Auditable;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'Ya existe un usuario con este email.')]
#[UniqueEntity(fields: ['dni'], message: 'Ya existe una cuenta con este DNI.')]
class User implements UserInterface, PasswordAuthenticatedUserInterface, AuditableInterface, EquatableInterface
{
    use Auditable;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * Roles legacy en JSON. Los reemplaza la relacion con Rol; queda mientras dure
     * la transicion para poder volver atras, y se borra en una migracion posterior.
     *
     * @var list<string>
     *
     * @deprecated usar getRolesAsignados()
     */
    #[ORM\Column]
    private array $roles = [];

    /** @var Collection<int, UsuarioRol> */
    #[ORM\OneToMany(mappedBy: 'usuario', targetEntity: UsuarioRol::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $usuarioRoles;

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    private ?string $complete_name = null;

    #[ORM\Column(length: 20, unique: true)]
    private ?string $dni = null;

    public function __construct()
    {
        $this->usuarioRoles = new ArrayCollection();
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
        return (string) $this->dni;
    }

    /**
     * Los roles de seguridad salen de la tabla `rol`: cada rol asignado aporta
     * ROLE_<CODIGO>, y la jerarquia de security.yaml los conecta con los roles
     * viejos (ROLE_ADMINISTRADOR -> ROLE_ADMIN, etc).
     *
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = ['ROLE_USER'];

        foreach ($this->usuarioRoles as $asignacion) {
            $rol = $asignacion->getRol();
            if ($rol && $rol->estaActivo()) {
                $roles[] = 'ROLE_'.strtoupper((string) $rol->getCodigo());
            }
        }

        return array_values(array_unique($roles));
    }

    /**
     * @param list<string> $roles
     *
     * @deprecated escribe la columna legacy; para asignar permisos usar addRol()
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /** @return list<string> Roles legacy en JSON, solo para la migracion. */
    public function getRolesLegacy(): array
    {
        return $this->roles;
    }

    /** @return Collection<int, UsuarioRol> */
    public function getUsuarioRoles(): Collection
    {
        return $this->usuarioRoles;
    }

    /** @return list<Rol> */
    public function getRolesAsignados(): array
    {
        $roles = [];
        foreach ($this->usuarioRoles as $asignacion) {
            if ($asignacion->getRol()) {
                $roles[] = $asignacion->getRol();
            }
        }

        return $roles;
    }

    public function tieneRol(Rol $rol): bool
    {
        foreach ($this->usuarioRoles as $asignacion) {
            if ($asignacion->getRol() === $rol) {
                return true;
            }
        }

        return false;
    }

    public function addRol(Rol $rol): static
    {
        if (!$this->tieneRol($rol)) {
            $this->usuarioRoles->add(new UsuarioRol($this, $rol));
        }

        return $this;
    }

    public function removeRol(Rol $rol): static
    {
        foreach ($this->usuarioRoles as $asignacion) {
            if ($asignacion->getRol() === $rol) {
                $this->usuarioRoles->removeElement($asignacion);
            }
        }

        return $this;
    }

    public function esAdministrador(): bool
    {
        foreach ($this->getRolesAsignados() as $rol) {
            if ($rol->esAdministrador() && $rol->estaActivo()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Claves de funcionalidad que el usuario tiene por sus roles.
     *
     * @return list<string>
     */
    public function getClaves(): array
    {
        $claves = [];
        foreach ($this->getRolesAsignados() as $rol) {
            if ($rol->estaActivo()) {
                $claves = array_merge($claves, $rol->getClaves());
            }
        }

        return array_values(array_unique($claves));
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Ensure the session doesn't contain actual password hashes by CRC32C-hashing them, as supported since Symfony 7.3.
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0".self::class."\0password"] = hash('crc32c', $this->password);

        return $data;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
    }

    public function getCompleteName(): ?string
    {
        return $this->complete_name;
    }

    public function setCompleteName(string $complete_name): static
    {
        $this->complete_name = $complete_name;
        return $this;
    }

    public function getDni(): ?string
    {
        return $this->dni;
    }

    public function setDni(string $dni): static
    {
        $this->dni = $dni;
        return $this;
    }

    /**
     * Symfony compara el usuario de la sesion contra el que acaba de leer de la
     * base en cada request; si no coinciden, cierra la sesion. Sin esto, dar de
     * baja a alguien que ya esta trabajando no lo saca: sigue navegando hasta
     * que la sesion expire sola.
     *
     * Ojo con el password: __serialize() lo guarda en la sesion hasheado con
     * CRC32C, asi que compararlo aca contra el hash de la base da siempre
     * distinto y cierra la sesion en cada request. De los cambios de password ya
     * se encarga Symfony por su cuenta.
     */
    public function isEqualTo(UserInterface $user): bool
    {
        if (!$user instanceof self) {
            return false;
        }

        return $this->getStatus() === $user->getStatus()
            && $this->getUserIdentifier() === $user->getUserIdentifier();
    }
}
