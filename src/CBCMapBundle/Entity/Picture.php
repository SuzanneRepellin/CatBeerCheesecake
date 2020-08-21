<?php

namespace CBCMapBundle\Entity;

use CBCMapBundle\Entity\CBC;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Ivory\GoogleMap\Map;
use Ivory\GoogleMap\Base\Coordinate;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;
use Ivory\GoogleMapBundle\Form\Type\PlaceAutocompleteType;

use Doctrine\ORM\Mapping as ORM;

/**
 * Picture
 *
 * @ORM\Table(name="picture")
 * @ORM\Entity(repositoryClass="CBCMapBundle\Repository\PictureRepository")
 */
class Picture
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
     * @ORM\Column(name="path", type="string", length=255, unique=true)
     */
    private $path;

    /**
     * @var int
     *
     * @ORM\Column(name="CBC_id", type="integer")
     */
    private $cBCId;

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
     * Set path
     *
     * @param string $path
     *
     * @return Picture
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set cBCId
     *
     * @param integer $cBCId
     *
     * @return Picture
     */
    public function setCBCId($cBCId)
    {
        $this->cBCId = $cBCId;

        return $this;
    }

    /**
     * Get cBCId
     *
     * @return int
     */
    public function getCBCId()
    {
        return $this->cBCId;
    }

    /**
     * Set authorId
     *
     * @param integer $authorId
     *
     * @return Picture
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

    public function savePicture($cbc_id, $picture, $author_id, $name)
    {
        $file_name = $picture->getClientOriginalName();
        $path_author_dir = 'images/' . (string)$author_id . '/';
        $path_cbc_dir = $path_author_dir . $name . '/';
        $path_file = $path_cbc_dir . $file_name;

        if (!is_dir($path_author_dir)) {
            mkdir($path_author_dir);
        }
        if (!is_dir($path_cbc_dir)) {
            mkdir($path_cbc_dir);
        }
        $suffix = 0;
        while (file_exists($path_cbc_dir . $file_name)) {
            $suffix += 1;
            $file_name = (string)$suffix . $file_name;
        }
        $picture->move($path_cbc_dir, $file_name);
        $this->setPath($path_cbc_dir . $file_name);
        $this->setCBCId($cbc_id);
        $this->setAuthorId($author_id);
    }
}

