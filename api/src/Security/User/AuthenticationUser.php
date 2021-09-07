<?php

// src/Security/User/WebserviceUser.php

namespace App\Security\User;

use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;


/**
 * @method string getUserIdentifier()
 */
class AuthenticationUser implements UserInterface, EquatableInterface
{
    /* The username display */
    private string $username;

    /* The first name of the user */
    private $firstName;

    /* The last name of the user */
    private $lastName;

    /* The first and last name of the user */
    private $name;

    /* Leave empty! */
    private $salt;

    /* Iether a BRP or CC person URI */
    private $roles;

    /* Always true */
    private $isActive;

    private $email;

    public function __construct(string $username = '', string $firstName = '', string $lastName = '', string $name = '', string $salt = null, array $roles = [], string $email = '', $locale = null)
    {
        $this->username = $username;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->name = $name;
        $this->salt = $salt;
        $this->roles = $roles;
        $this->isActive = true;
        $this->email = $email;
        $this->locale = $locale; // The language of this user
    }

    public function __toString()
    {
        return $this->name;
    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function getSalt()
    {
        return $this->salt;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getFirstName()
    {
        return $this->firstName;
    }

    public function getLastName()
    {
        return $this->lastName;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getLocale()
    {
        return $this->locale;
    }

    public function isEnabled()
    {
        return $this->isActive;
    }

    public function eraseCredentials()
    {
    }

    // serialize and unserialize must be updated - see below
    public function serialize()
    {
        return serialize([
            $this->username
            // see section on salt below
            // $this->salt,
        ]);
    }

    public function unserialize($serialized)
    {
        list(
            $this->username) = unserialize($serialized);
    }

    public function isEqualTo(UserInterface $user)
    {
        return true;
    }

    public function __call($name, $arguments)
    {
        // TODO: Implement @method string getUserIdentifier()
    }
}
