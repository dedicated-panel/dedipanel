<?php

namespace DP\MachineBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * DP\MachineBundle\Entity\Machine
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Machine
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var bigint $privateIp
     *
     * @ORM\Column(name="privateIp", type="bigint", nullable=true)
     * @Assert\Ip(message="message.assert.privateIp")
     */
    private $privateIp;

    /**
     * @var bigint $publicIp
     *
     * @ORM\Column(name="publicIp", type="bigint", nullable=true)
     * @Assert\Ip(message="message.assert.publicIp")
     */
    private $publicIp;

    /**
     * @var integer $port
     *
     * @ORM\Column(name="port", type="integer")
     * @Assert\Min(limit=1, message="message.assert.port")
     * @Assert\Min(limit=65536, message="message.assert.port")
     */
    private $port;

    /**
     * @var string $user
     *
     * @ORM\Column(name="user", type="string", length=16)
     * @Assert\NotBlank(message="message.assert.user")
     */
    private $user;
    
    /**
     * @var string $passwd
     * 
     * @Assert\NotBlank(message="message.assert.passwd")
     */
    private $passwd;

    /**
     * @var string $pubkeyHash
     *
     * @ORM\Column(name="pubkeyHash", type="string", length=40)
     */
    private $pubkeyHash;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set privateIp
     *
     * @param bigint $privateIp
     */
    public function setPrivateIp($privateIp)
    {
        $this->privateIp = $privateIp;
    }

    /**
     * Get privateIp
     *
     * @return bigint 
     */
    public function getPrivateIp()
    {
        return $this->privateIp;
    }

    /**
     * Set publicIp
     *
     * @param bigint $publicIp
     */
    public function setPublicIp($publicIp)
    {
        $this->publicIp = $publicIp;
    }

    /**
     * Get publicIp
     *
     * @return bigint 
     */
    public function getPublicIp()
    {
        return $this->publicIp;
    }

    /**
     * Set port
     *
     * @param integer $port
     */
    public function setPort($port)
    {
        $this->port = $port;
    }

    /**
     * Get port
     *
     * @return integer 
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Set user
     *
     * @param string $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * Get user
     *
     * @return string 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set pubkeyHash
     *
     * @param string $pubkeyHash
     */
    public function setPubkeyHash($pubkeyHash)
    {
        $this->pubkeyHash = $pubkeyHash;
    }

    /**
     * Get pubkeyHash
     *
     * @return string 
     */
    public function getPubkeyHash()
    {
        return $this->pubkeyHash;
    }

    /**
     * Set password
     * 
     * @param string $passwd
     */
    public function setPasswd($passwd) {
        $this->passwd = $passwd;
    }
    /**
     * Get password
     * 
     * @return string
     */
    public function getPasswd() {
        return $this->passwd;
    }
}
?>