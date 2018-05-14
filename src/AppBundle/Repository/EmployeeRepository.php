<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 09.05.18
 * Time: 15:57
 */

namespace AppBundle\Repository;


use Doctrine\ORM\EntityRepository;

class EmployeeRepository extends  EntityRepository
{
    public function getEmployeesQueryWithSearch($search)
    {
        $em = $this->getEntityManager();
        $dql = "SELECT e, p
                 FROM AppBundle:Employee e LEFT JOIN e.position p
                 WHERE (e.name like :s
                 OR p.name like :s
                 OR e.salary like :s
                 OR e.employmantDate like :s)";
        return $query = $em->createQuery($dql)->setParameter('s','%'.$search.'%' );
    }

    public function getAllEmployeesQuery()
    {
        $em = $this->getEntityManager();
        $dql = "SELECT e,p  FROM AppBundle:Employee e LEFT JOIN e.position p ORDER BY e.id DESC";
        return $query = $em->createQuery($dql);
    }

//    Get count subordinates in one chief
    public function countSubordinatesWithChief($id)
    {
        return $this->createQueryBuilder('s')
            ->select('count(s)')
            ->where('s.parent = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getEmployeesQueryWithSearchAjax($search)
    {
        $em = $this->getEntityManager();
        $dql = "SELECT e, p
                 FROM AppBundle:Employee e LEFT JOIN e.position p
                 WHERE (e.name like :s
                 OR p.name like :s
                 OR e.salary = :s2
                 OR e.employmantDate like :s2)";
        return $em->createQuery($dql)->setMaxResults(100)->setParameter('s','%'.$search.'%' )
            ->setParameter('s2', $search)->getResult();
    }

}