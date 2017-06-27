<?php

namespace AppBundle\Entity;

/**
 * Analytics
 */
class Analytics
{
    /**
     * @var guid
     */
    private $id;

    /**
     * @var integer
     */
    private $created;

    /**
     * @var string
     */
    private $event_type;

    /**
     * @var string
     */
    private $session_id;

    public function __construct(Session $Session, $event, $label, $category)
    {
        $this->setSession($Session);
        $this->setEventType($event);
        $this->setLabel($label);
        $this->setCategory($category);
        $this->setCreated(time());
    }

    /**
     * @var string
     */
    private $label;


    /**
     * Get id
     *
     * @return guid
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set created
     *
     * @param integer $created
     *
     * @return Analytics
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return integer
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set eventType
     *
     * @param string $eventType
     *
     * @return Analytics
     */
    public function setEventType($eventType)
    {
        $this->event_type = $eventType;

        return $this;
    }

    /**
     * Get eventType
     *
     * @return string
     */
    public function getEventType()
    {
        return $this->event_type;
    }

    /**
     * Set sessionId
     *
     * @param string $sessionId
     *
     * @return Analytics
     */
    public function setSessionId($sessionId)
    {
        $this->session_id = $sessionId;

        return $this;
    }

    /**
     * Get sessionId
     *
     * @return string
     */
    public function getSessionId()
    {
        return $this->session_id;
    }

    /**
     * Set label
     *
     * @param string $label
     *
     * @return Analytics
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }
    /**
     * @var string
     */
    private $category;

    /**
     * @var \AppBundle\Entity\Session
     */
    private $Session;


    /**
     * Set category
     *
     * @param string $category
     *
     * @return Analytics
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set session
     *
     * @param \AppBundle\Entity\Session $session
     *
     * @return Analytics
     */
    public function setSession(\AppBundle\Entity\Session $session = null)
    {
        $this->Session = $session;

        return $this;
    }

    /**
     * Get session
     *
     * @return \AppBundle\Entity\Session
     */
    public function getSession()
    {
        return $this->Session;
    }
}
