<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 09.05.18
 * Time: 11:25
 */

namespace AppBundle\Form;

use AppBundle\Entity\Employee;
use AppBundle\Entity\Position;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmployeeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $employeeId = $options['data']->getId();

        $builder
            ->add('name', TextType::class, [
                    'label' => 'Name',
                    'required' => true,
                    'attr' => [
                        'class' => 'form-control'
                    ]
                ])
            ->add('position', EntityType::class, [
                'class' => Position::class,
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('p')
                        ->where('p.name != ?1')
                        ->setParameter(1, 'General director');
                },
                'choice_label' => 'name',
                'label' => 'Position',
                'required' => true,
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('salary', IntegerType::class, [
                'label' => 'Salary',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'min' => '1'
                ]
            ])
            ->add('employmantDate', DateType::class, [
                'label' => 'Employmant date',
                'widget' => 'single_text',
                'required' => true,
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('parent', EntityType::class, [
                'class' => Employee::class,
                'query_builder' => function (EntityRepository $er) use ($employeeId){
                    if ($employeeId) {
                        return $er->createQueryBuilder('e')
                            ->leftJoin('e.position', 'p')
                            ->where('e.id != ?1')
                            ->andWhere('p.name != ?2')
                            ->setParameter(1, $employeeId)
                            ->setParameter(2, 'Software Engineer');
                    } else {
                        return $er->createQueryBuilder('e')
                            ->leftJoin('e.position', 'p')
                            ->where('p.name != ?1')
                            ->setParameter(1, 'Software Engineer');;
                    }
                },
                'choice_label' => 'name',
                'label' => 'Chief',
                'required' => true,
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('image', FileType::class, [
                    'data_class' => null,
                    'label' => 'Image',
                    'required' => false,
                    'attr' => [
                        'class' => 'form-control-file'
                    ]
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Employee::class
        ]);
    }
}