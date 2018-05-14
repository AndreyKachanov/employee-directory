<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 08.05.18
 * Time: 20:35
 */

namespace AppBundle\DataFixtures\ORM;


use AppBundle\Entity\Employee;
use AppBundle\Entity\Position;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadFixtures implements FixtureInterface
{

    public function load(ObjectManager $manager)
    {
        ini_set('memory_limit', '-1');

        // Должности
        $positions = ['General director', 'Head of the directorate', 'Deputy Director', 'Head of Department', 'Deputy Head of Department', 'Software Engineer'];
        // Имена
        $names = ['Vasia Pusichkin', 'Petia Dragluk', 'Denis Bichkov', 'Ivan Ivanov', 'Valera Vasin', 'Sava Petrenko'];
        // Фотографии сотрудников
        $images = ['1.jpg', '2.jpg', '3.jpg', '4.jpg', '5.jpg'];

        foreach ($positions as $key => $value) {
            $position[$key] = new Position();
            $position[$key]->setName($value);
            $manager->persist($position[$key]);
        }
            $manager->flush();

        //Создание генерального директора
        $generalDirector = new Employee();

        $positionGeneralDir = $manager->getRepository(Position::class)->findOneByName('General Director');

        $generalDirector->setName('Igor Smilanskiy');
        $generalDirector->setPosition($positionGeneralDir);
        $generalDirector->setImage('1.jpg');
        $generalDirector->setSalary(100000);
        $generalDirector->setEmploymantDate(new \DateTime('30 years ago'));

        $manager->persist($generalDirector);
        $manager->flush();

        //Создание директоров областных дирекций. 26 шт
        for ($i = 0; $i < 26; $i++) {

            $employee = new Employee();

            $parent = $manager->getRepository(Employee::class)->findOneBy(['position' => $positionGeneralDir]);

            $employee->setName($names[array_rand($names)]);
            $employee->setSalary(rand(50000,75000));
            $employee->setImage($images[array_rand($images)]);
            $employee->setEmploymantDate(new \DateTime("$i years ago"));

            $position = $manager->getRepository(Position::class)->findOneByName('Head of the directorate');
            $employee->setPosition($position);
            $employee->setParent($parent);

            $manager->persist($employee);
        }

        $manager->flush();

        // Cоздание заместителей областных дирекций. По 5 шт
        $positionHeadDir = $manager->getRepository(Position::class)->findOneByName('Head of the directorate');

        $employeesHeadDir = $manager->getRepository(Employee::class)->findBy(['position' => $positionHeadDir]);

        foreach ($employeesHeadDir as $headDir) {

            for ($j = 1; $j < 5; $j++) {

                $employee = new Employee();

                $employee->setName($names[array_rand($names)]);
                $employee->setSalary(rand(30000, 50000));
                $employee->setImage($images[array_rand($images)]);
                $employee->setEmploymantDate(new \DateTime("now - " . rand(1, 12) ." months"));

                $position = $manager->getRepository(Position::class)->findOneByName('Deputy Director');
                $employee->setPosition($position);

                $employee->setParent($headDir);

                $manager->persist($employee);
            }
            $manager->flush();
        }

        //Создание начальников отделов. По 10 шт
        $positionDeputyDir = $manager->getRepository(Position::class)->findOneByName('Deputy Director');
        $employeesDeputyDir = $manager->getRepository(Employee::class)->findBy(['position' => $positionDeputyDir]);

        foreach ($employeesDeputyDir as $deputyDir) {

            for ($j = 0; $j < 10; $j++) {

                $employee = new Employee();

                $employee->setName($names[array_rand($names)]);
                $employee->setSalary(rand(15000, 30000));
                $employee->setImage($images[array_rand($images)]);
                $employee->setEmploymantDate(new \DateTime("now - " . rand(1, 12) ." months"));

                $position = $manager->getRepository(Position::class)->findOneByName('Head of Department');
                $employee->setPosition($position);

                $employee->setParent($deputyDir);

                $manager->persist($employee);
            }

            $manager->flush();
        }

        //Создание заместителей начальников, по 2 шт
        $positionHeadDepart = $manager->getRepository(Position::class)->findOneByName('Head of Department');
        $employeesHeadDepart = $manager->getRepository(Employee::class)->findBy(['position' => $positionHeadDepart]);

        foreach ($employeesHeadDepart as $headDepart) {

            for ($j = 0; $j < 3; $j++) {

                $employee = new Employee();

                $employee->setName($names[array_rand($names)]);
                $employee->setSalary(rand(10000, 15000));
                $employee->setImage($images[array_rand($images)]);
                $employee->setEmploymantDate(new \DateTime("now - " . rand(1, 12) ." months"));

                $position = $manager->getRepository(Position::class)->findOneByName('Deputy Head of Department');
                $employee->setPosition($position);

                $employee->setParent($headDepart);

                $manager->persist($employee);
            }

            $manager->flush();
        }

        //Создание инженеров. По 12 шт.
        $positionDutyHead = $manager->getRepository(Position::class)->findOneByName('Deputy Head of Department');
        $employeesDutyHead = $manager->getRepository(Employee::class)->findBy(['position' => $positionDutyHead]);

        foreach ($employeesDutyHead as $headDepart) {

            for ($j = 0; $j < 15; $j++) {

                $employee = new Employee();

                $employee->setName($names[array_rand($names)]);
                $employee->setSalary(rand(7500, 10000));
                $employee->setImage($images[array_rand($images)]);
                $employee->setEmploymantDate(new \DateTime("now - " . rand(1, 12) ." months"));

                $position = $manager->getRepository(Position::class)->findOneByName('Software Engineer');
                $employee->setPosition($position);

                $employee->setParent($headDepart);

                $manager->persist($employee);
            }

            $manager->flush();
        }

    }

}