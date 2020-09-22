<?php

namespace App\Controller\Admin;

use App\Entity\Appointment;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityUpdater;
use EasyCorp\Bundle\EasyAdminBundle\Router\CrudUrlGenerator;

class AppointmentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Appointment::class;
    }

    public function configureFields(string $pageName): iterable
    {
    	$lawyers = $this->getDoctrine()
						->getRepository(User::class)
						->findByRole('ROLE_LAWYER');
    	$lawyersArr = $lawyers->filter(function(User $lawyers) {
			return $lawyers->getEmail();
		});
    	return [
			DateField::new('book_date'),
			ChoiceField::new('book_hour')->setChoices(array_combine(Appointment::BUSINESS_HOURS, Appointment::BUSINESS_HOURS)),
			DateTimeField::new('created_at')->onlyOnDetail(),
			DateTimeField::new('updated_at')->onlyOnDetail(),
            //TextField::new('status')->onlyOnIndex(),
			ChoiceField::new('status')->setChoices(['Pending'=>'pending', 'Approved'=>'approved', 'Rejected'=>'rejected'])->hideOnForm(),
			//AssociationField::new('lawyer')
			AssociationField::new('lawyer')->setFormTypeOptions([
				"choices" => $lawyersArr
			])
        ];
    }
	
	public function configureFilters(Filters $filters): Filters
	{
		return $filters
			->add(ChoiceFilter::new('status', 'Status')->setChoices(['Pending'=>'pending', 'Approved'=>'approved', 'Rejected'=>'rejected']))
			;
	}
	
	public function configureActions(Actions $actions): Actions
	{
		$approveAppointment = Action::new('approveAppointment', 'Approve', 'fa fa-check text-success')
									->linkToCrudAction('approveAppointment')
									->displayIf(static function ($entity) {
										return $entity->getStatus() == Appointment::STATUS_PENDING;
									});
		$rejectAppointment = Action::new('rejectAppointment', 'Reject', 'fa fa-times text-danger')
									->linkToCrudAction('rejectAppointment')
									->displayIf(static function ($entity) {
										return $entity->getStatus() == Appointment::STATUS_PENDING;
									});
		
		return $actions
			->disable(Action::DELETE)
			->setPermission(Action::NEW, 'ROLE_CITIZEN')
			->setPermission(Action::EDIT, 'ROLE_CITIZEN')
			->setPermission($approveAppointment, 'ROLE_LAWYER')
			->setPermission($rejectAppointment, 'ROLE_LAWYER')
			->add(Crud::PAGE_INDEX, $rejectAppointment)
			->add(Crud::PAGE_INDEX, $approveAppointment)
			->add(Crud::PAGE_INDEX, Action::DETAIL)
			//->add(Crud::PAGE_EDIT, Action::SAVE_AND_ADD_ANOTHER)
			//->remove(Crud::PAGE_INDEX, Action::DELETE)
			->remove(Crud::PAGE_DETAIL, Action::EDIT)
			->remove(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE)
			;
	}
	
	public function approveAppointment(AdminContext $context) {
		$entityDto = $context->getEntity();
		$event = new BeforeEntityUpdatedEvent($entityDto->getInstance());
		$entityInstance = $event->getEntityInstance();
		
		$this->get(EntityUpdater::class)->updateProperty($entityDto, 'status', Appointment::STATUS_APPROVED);
		$this->updateEntity($this->get('doctrine')->getManagerForClass($entityDto->getFqcn()), $entityInstance);
		
		$url = $context->getReferrer()
			?? $this->get(CrudUrlGenerator::class)->build()->setAction(Action::INDEX)->generateUrl();
		
		return $this->redirect($url);
	}
	
	public function rejectAppointment(AdminContext $context) {
		$entityDto = $context->getEntity();
		$event = new BeforeEntityUpdatedEvent($entityDto->getInstance());
		$entityInstance = $event->getEntityInstance();
		
		$this->get(EntityUpdater::class)->updateProperty($entityDto, 'status', Appointment::STATUS_REJECTED);
		$this->updateEntity($this->get('doctrine')->getManagerForClass($entityDto->getFqcn()), $entityInstance);
		
		$url = $context->getReferrer()
			?? $this->get(CrudUrlGenerator::class)->build()->setAction(Action::INDEX)->generateUrl();
		
		return $this->redirect($url);
	}
	
	public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
	{
		$user = $this->get('security.token_storage')->getToken()->getUser();
		
		parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
		
		$response = $this->get(EntityRepository::class)->createQueryBuilder($searchDto, $entityDto, $fields, $filters);
		
		if($this->isGranted('ROLE_CITIZEN')){
			$response->where('entity.createdBy = :user')->setParameter('user', $user);
		}
		
		if($this->isGranted('ROLE_LAWYER')){
			$response->where('entity.lawyer = :user')->setParameter('user', $user);
		}
		
		return $response;
	}
}
