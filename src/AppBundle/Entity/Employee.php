<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 08.05.18
 * Time: 19:28
 */

namespace AppBundle\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\EmployeeRepository")
 * @ORM\Table(name="employee")
 */
class Employee
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(
     *     min = 5,
     *     max = 30,
     *     minMessage = "Name must be at least {{ limit }} characters long.",
     *     maxMessage = "Name cannot be longer than {{ limit }} characters."
     * )
     * @Assert\Regex(
     *     pattern="/[a-zA-Z]/",
     *     message="This field must contain only Latin characters."
     * )
     * @ORM\Column(type="string", length=500)
     */
    private $name;

    /**
     * @Assert\NotBlank()
     * @ORM\ManyToOne(targetEntity="Position", inversedBy="employees")
     * @ORM\JoinColumn(name="position_id", referencedColumnName="id")
     */
    private $position;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="date")
     */
    private $employmantDate;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="integer")
     */
    private $salary;

    /**
     * @Assert\File(
     *     maxSize = "5M",
     *     maxSizeMessage = "Image size should not exceed 5 mb"
     * ),
     * @Assert\Image(
     *     mimeTypes = {"image/jpg", "image/jpeg", "image/png"},
     *     mimeTypesMessage="Invalid file type (the allowed formats are jpg, jpeg, png)",
     *     maxWidth = 500,
     *     maxHeight = 500,
     *     maxWidthMessage = "The width of the image should not exceed 500px",
     *     maxHeightMessage = "The height of the photo should not exceed 500px"
     * )
     * @ORM\Column(type="string", nullable=true)
     */
    private $image;

    /**
     * @Assert\NotBlank()
     * @ORM\ManyToOne(targetEntity="Employee", inversedBy="subordinates")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="Employee", mappedBy="parent")
     */
    private $subordinates;

    public function __construct()
    {
        $this->subordinates = new ArrayCollection();
        $this->employmantDate = new \DateTime('now');
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param mixed $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @return mixed
     */
    public function getEmploymantDate()
    {
        return $this->employmantDate;
    }

    /**
     * @param mixed $employmantDate
     */
    public function setEmploymantDate($employmantDate)
    {
        $this->employmantDate = $employmantDate;
    }

    /**
     * @return mixed
     */
    public function getSalary()
    {
        return $this->salary;
    }

    /**
     * @param mixed $salary
     */
    public function setSalary($salary)
    {
        $this->salary = $salary;
    }

    /**
     * @return mixed
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param mixed $image
     */
    public function setImage($image)
    {
        $this->image = $image;
    }

    /**
     * @return mixed
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param mixed $parent
     */
    public function setParent(\AppBundle\Entity\Employee $parent = null)
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * Add subordinate
     *
     * @param \AppBundle\Entity\Employee $subordinate
     *
     * @return Employee
     */
    public function addSubordinate(\AppBundle\Entity\Employee $subordinate)
    {
        $this->subordinates[] = $subordinate;

        return $this;
    }

    /**
     * Remove subordinate
     *
     * @param \AppBundle\Entity\Employee $subordinate
     */
    public function removeSubordinate(\AppBundle\Entity\Employee $subordinate)
    {
        $this->subordinates->removeElement($subordinate);
    }

    /**
     * Get subordinates
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSubordinates()
    {
        return $this->subordinates;
    }

    public function __toString() {
        return $this->name;
    }
}
