<?php

namespace CBCMapBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ivory\GoogleMap\Base\Coordinate;

/**
 * CBC
 *
 * @ORM\Table(name="c_b_c")
 * @ORM\Entity(repositoryClass="CBCMapBundle\Repository\CBCRepository")
 */
class CBC
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="category", type="string", length=20)
     */
    private $category;

    /**
     * @var string
     *
     * @ORM\Column(name="coordinates", type="string", length=255)
     */
    private $coordinates;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", length=255, nullable=true)
     */
    private $address;

    /**
     * @var bool
     *
     * @ORM\Column(name="pictures_available", type="boolean")
     */
    private $picturesAvailable;

    /**
     * @var int
     *
     * @ORM\Column(name="author_id", type="integer")
     */
    private $authorId;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return CBC
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set category
     *
     * @param string $category
     *
     * @return CBC
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
     * Set coordinates
     *
     * @param string $coordinates
     *
     * @return CBC
     */
    public function setCoordinates($coordinates)
    {
        $this->coordinates = $coordinates;

        return $this;
    }

    /**
     * Get coordinates
     *
     * @return string
     */
    public function getCoordinates()
    {
        return $this->coordinates;
    }
    
    /**
     * Get coordinates as coordinates
     *
     * @return Coordinate
     */
    public function getCoordinatesAsCoordinate()
    {
        $values = explode(',', $this->coordinates);
        if (count($values) != 2)
        {
            return new Coordinate(0,0);
        }
        $coordos =  new Coordinate((float)$values[0], (float)$values[1]);
        return $coordos;
    }

    /**
     * Set address
     *
     * @param string $address
     *
     * @return CBC
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set picturesAvailable
     *
     * @param boolean $picturesAvailable
     *
     * @return CBC
     */
    public function setPicturesAvailable($picturesAvailable)
    {
        $this->picturesAvailable = $picturesAvailable;

        return $this;
    }

    /**
     * Get picturesAvailable
     *
     * @return bool
     */
    public function getPicturesAvailable()
    {
        return $this->picturesAvailable;
    }

    /**
     * Set authorId
     *
     * @param integer $authorId
     *
     * @return CBC
     */
    public function setAuthorId($authorId)
    {
        $this->authorId = $authorId;

        return $this;
    }

    /**
     * Get authorId
     *
     * @return int
     */
    public function getAuthorId()
    {
        return $this->authorId;
    }
}

