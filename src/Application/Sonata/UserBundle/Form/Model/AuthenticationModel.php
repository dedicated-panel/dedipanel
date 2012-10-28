<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Sonata\UserBundle\Form\Model;

use Application\Sonata\UserBundle\Entity\User;

class AuthenticationModel
{    
    public $user;
    
    /**
     * @var string
     */
    public $new;
    
    public $username;
    
    public $email;
    
    public $current_password;
    
    public function __construct(User $user)
    {
        $this->username = $user->getUsername();
        $this->email = $user->getEmail();
    }
}
