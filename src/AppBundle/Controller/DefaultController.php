<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Employee;
use AppBundle\Form\EmployeeType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use WhiteOctober\BreadcrumbsBundle\Model\Breadcrumbs;

class DefaultController extends Controller
{

    /**
     * @Route("/", name="index")
     */
    public function indexAction(EntityManagerInterface $em)
    {
        return $this->render('default/index.html.twig');
    }

    /**
     * @Route("/admin", name="admin_index")
     */
    public function listAction(Request $request, EntityManagerInterface $em, Breadcrumbs $breadcrumbs)
    {
        $breadcrumbs->addItem("Tree", $this->get("router")->generate("index"));
        $breadcrumbs->addItem("Employees");
        $query = '';

        if ($request->get('s')) {
            $search = $request->get('s');
            $query = $em->getRepository(Employee::class)->getEmployeesQueryWithSearch($search);
        } else
            $query = $em->getRepository(Employee::class)->getAllEmployeesQuery();

        $paginator  = $this->get('knp_paginator');

        $employees = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            10/*limit per page*/
        );

        return $this->render('employees/employees_list.html.twig', [
            'employees' => $employees
        ]);
    }

    /**
     * @Route("/admin/employee/new/", name="admin_employee_new")
     */
    public function newAction(Request $request, EntityManagerInterface $em, Breadcrumbs $breadcrumbs)
    {
        $breadcrumbs->addItem("Tree", $this->get("router")->generate("index"));
        $breadcrumbs->addItem("Employees", $this->get("router")->generate("admin_index"));
        $breadcrumbs->addItem("New employee");
        $employee = new Employee();

        $form = $this->createForm(EmployeeType::class, $employee);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $file = $employee->getImage();

            if (!empty($file)) {
                $fileName = md5(uniqid()) . '.' . $file->guessExtension();
                $file->move($this->getParameter('employees_images_directory'), $fileName);
                $employee->setImage($fileName);
            }

            $em->persist($employee);
            $em->flush();

            return $this->redirectToRoute('admin_index');
        }

        return $this->render('employees/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/employee/edit/{id}", name="admin_employee_edit")
     */
    public function editAction(Request $request, Employee $employee, EntityManagerInterface $em, Breadcrumbs $breadcrumbs)
    {
        $breadcrumbs->addItem("Tree", $this->get("router")->generate("index"));
        $breadcrumbs->addItem("Employees", $this->get("router")->generate("admin_index"));
        $breadcrumbs->addItem("Edit employee");

        if ($employee->getPosition()->getName() == 'General director')
            throw $this->createNotFoundException('The employee does not exist');

        $oldImage = $employee->getImage();

        $form = $this->createForm(EmployeeType::class, $employee);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $imageFromForm = $employee->getImage();

            if (empty($imageFromForm))
                $employee->setImage($oldImage);
            else {
                $fileName = md5(uniqid()) . '.' . $imageFromForm->guessExtension();
                $imageFromForm->move($this->getParameter('employees_images_directory'), $fileName);
                $employee->setImage($fileName);
            }

            $em->flush();

            return $this->redirectToRoute('admin_index');
        }

        return $this->render('employees/edit.html.twig', [
            'employee' => $employee,
            'form' => $form->createView(),
            'id' => $employee->getId()
        ]);
    }

    /**
     * @Route("/admin/employee/delete", name="admin_employee_delete")
     */
    public function deleteAction(Request $request, EntityManagerInterface $em)
    {
        $oldChief = $em->getRepository(Employee::class)->findOneById($request->get('delete'));
        $newChief = $em->getRepository(Employee::class)->findOneById($oldChief->getParent()->getId());

        foreach ($oldChief->getSubordinates()->getValues() as $employee) {
            $employee->setParent($newChief);
            $em->persist($employee);
        }

        $em->remove($oldChief);
        $em->flush();

        return $this->redirectToRoute('admin_index');
    }

    /**
     * @Route("/admin/chief/{id}/count_subordinates", name="count_subordinates")
     * @Method({"POST"})
     */
    public function countSubordinatesWithChiefAction(Employee  $employee, EntityManagerInterface $em)
    {
        $subordinates = $em->getRepository(Employee::class)->findBy(['parent' => $employee]);

        if (count($subordinates) < 1 ) {

            $em->remove($employee);
            $em->flush();

            return new JsonResponse(false, 200);
        }

        $jsonArray = [];
        foreach ($subordinates as $s)
            $jsonArray[] = $s->getName();

        return new JsonResponse([
            'countSubordinates' => count($subordinates),
            'subordinates' => $jsonArray,
            'parent' => $employee->getParent()->getName()
        ], 200);
    }

    /**
     * @Route("/search/employee", name="ajax_search_employee")
     * @Method({"POST"})
     */
    public function searchAjaxAction(Request $request, EntityManagerInterface $em)
    {

        if ($request->get('s')) {
            $search = $request->get('s');
            $employees = $em->getRepository(Employee::class)->getEmployeesQueryWithSearchAjax($search);
        } else
            return new JsonResponse(false, 200);

        if (count($employees) > 0) {
            $jsonArray = [];
            foreach ($employees as $e) {
                $temp['id'] = $e->getId();
                $temp['name'] = $e->getName();
                $temp['position'] = $e->getPosition()->getName();
                $temp['salary'] = $e->getSalary();
                $temp['employmantDate'] = $e->getEmploymantDate()->format('Y-m-d');
                $temp['image'] = ( ($e->getImage() == null) ? 'no-image.png': $e->getImage() );
                $temp['parent'] = ( ($e->getParent()) ? 'parent' : 'no_parent');
                $jsonArray[] = $temp;
            }

            return new JsonResponse([
                'json' => $jsonArray,
            ], 200);

        } else {
            return new JsonResponse([
                'jsondata' => 'not_found',
            ], 200);
        }
    }

    /**
     * @Route("/search/child/", name="search_child")
     * @Method({"GET"})
     */
    public function getChildByParentAction(Request $request, EntityManagerInterface $em)
    {
        if ( $request->get('id') === "#" ) {
            $data = $em->getRepository(Employee::class)->findBy(['parent' => null]);
            $newArray = [];

            foreach ($data as $e) {
                $temp['id'] = $e->getId();
                $temp['salary'] = $e->getSalary();
                $temp['emp_date'] = $e->getEmploymantDate()->format('Y-m-d');;
                $temp['parent'] = "#";
                $temp['text']  = "<span class='node-name'>" .
                                    $e->getPosition()->getName() . " - " . $e->getName() .
                                "</span>".
                                "<span class='node-item'>Salary - " . $e->getSalary() .
                                    "; Employmant date - " . $e->getEmploymantDate()->format('Y-m-d') .
                                ";</span>";

                $temp['children'] = true;
                $newArray[] = $temp;
            }

        } else {
            $idParent = $request->get('id') ;

            $parent = $em->getRepository(Employee::class)->findById($idParent);

            $employeesByParent = $em->getRepository(Employee::class)->findBy(['parent' => $parent]);

            $newArray = [];

            foreach ($employeesByParent as $e) {
                $temp = [];
                $temp['id'] = $e->getId();
                $temp['salary'] = $e->getSalary();
                $temp['emp_date'] = $e->getEmploymantDate()->format('Y-m-d');;
                $temp['parent'] = $idParent;

                $temp['text']  = "<span class='node-name'>" .
                    $e->getPosition()->getName() . " - " . $e->getName() .
                    "</span>".
                    "<span class='node-item'>Salary - " . $e->getSalary() .
                    "; Employmant date - " . $e->getEmploymantDate()->format('Y-m-d') .
                    ";</span>";

                $childrens = $em->getRepository(Employee::class)->findBy(['parent' => $e->getId()]);
                if (count($childrens) > 0) $temp['children'] = true;

                $newArray[] = $temp;

            }
        }

        return new JsonResponse($newArray, 200);
    }
}
