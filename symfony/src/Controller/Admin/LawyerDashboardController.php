<?php

namespace App\Controller\Admin;

use App\Entity\Appointment;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\CrudUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LawyerDashboardController extends AbstractDashboardController
{
    /**
     * @Route("/admin", name="admin")
     */
    public function index(): Response
    {
        #return parent::index();
		$routeBuilder = $this->get(CrudUrlGenerator::class)->build();
	
		return $this->redirect($routeBuilder->setController(AppointmentCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('District Attorneys')
			->setFaviconPath('img/favicon.svg');
    }

    public function configureMenuItems(): iterable
    {
        //yield MenuItem::linktoDashboard('Dashboard', 'fa fa-home');
		yield MenuItem::section('Appointments');
		yield MenuItem::linkToCrud('List', 'fa fa-list', Appointment::class);
		yield MenuItem::linkToCrud('Add', 'fa fa-plus', Appointment::class)->setAction('new')->setPermission('ROLE_CITIZEN');
    }
}
