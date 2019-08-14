<?php

namespace Onetoweb\Transsmart;

/**
 * Transsmart Token
 */
class Token
{
    /**
     * @var string
     */
    private $token;
    
    /**
     * @var \DateTime
     */
    private $expiresAt;
    
    /**
     * @param string $token
     * @param \DateTime $expiresAt = null (optional default to +23:55 hours)
     */
    public function __construct($token, \DateTime $expiresAt = null)
    {
        $this->token = $token;
        
        if ($expiresAt == null) {
            $this->expiresAt = new \DateTime('+23 hours +55 minutes');
        }
    }
    
    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }
    
    /**
     * @return \DateTime 
     */
    public function getExpiresAt()
    {
        return $this->expiresAt;
    }
    
    /**
     *  @return \DateInterval
     */
    public function getExpiresIn()
    {
        return $this->expiresAt->diff(new \DateTime());
    }
    
    /**
     * @return bool
     */
    public function hasExpired()
    {
        return (bool) ($this->expiresAt < new \DateTime());
    }
    
    /**
     * @return string
     */
    public function serialize()
    {
        return serialize($this);
    }
}