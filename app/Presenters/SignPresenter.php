<?php
namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;
use App\Model\PostFacade;
use App\Model\UserFacade;


final class SignPresenter extends Nette\Application\UI\Presenter
{
	private PostFacade $facade;
	private UserFacade $userFacade;

	public function __construct(PostFacade $facade, UserFacade $userFacade)
	{
		$this->facade = $facade;
		$this->userFacade = $userFacade;
	}
	protected function createComponentSignInForm(): Form
	{
		$form = new Form;
		$form->addText('username', 'Uživatelské jméno:')
			->setRequired('Prosím vyplňte své uživatelské jméno.');

		$form->addPassword('password', 'Heslo:')
			->setRequired('Prosím vyplňte své heslo.');

		$form->addSubmit('send', 'Přihlásit');

		$form->onSuccess[] = [$this, 'signInFormSucceeded'];
		return $form;
	}
    public function signInFormSucceeded(Form $form, \stdClass $data): void
{
	try {
		$this->getUser()->login($data->username, $data->password);
		$this->redirect('Homepage:');

	} catch (Nette\Security\AuthenticationException $e) {
		$form->addError('Nesprávné přihlašovací jméno nebo heslo.');
	}
}
public function actionOut(): void
{
	$this->getUser()->logout();
	$this->flashMessage('Odhlášení bylo úspěšné.');
	$this->redirect('Homepage:');
}
public function createComponentRegisterForm(): Form
{
	$form = new Form;
	$form->addText('username', 'Uživatelské jméno:')
		->setRequired('Prosím vyplňte své uživatelské jméno.');
	$form->addEmail('email', 'Email:')
		->setRequired('Prosím vyplňte svůj email.');
	$form->addpassword('password', 'Heslo:')
		->setRequired('Prosím vyplňte své heslo.');
	
	$form->addSubmit('send', 'Registrovat');
	$form->onSuccess[] = [$this, 'onRegisterSuccess'];
	return $form;
}
public function onRegisterSuccess(Form $form, \stdClass $data): void
{
	$this->userFacade->add($data->username, $data->email, $data->password);
	$this->flashMessage('Registrace byla úspěšná.');
	$this->redirect('Homepage:');
}
public function createComponentChangeForm(): Form
{
	$user = $this->getUser()->getIdentity();
	$form = new Form;
	$form->addText('username', 'Uživatelské jméno:')	
	     ->setValue($user->data['username'])
		 ->setrequired('Prosím vyplňte své uživatelské jméno.');
	$form->addText('email', 'Email:')
		 ->setValue($user->data['email'])
		 ->setrequired('Prosím vyplňte svůj email.');
	$form->addpassword('password', 'Heslo:');
	$form->addSubmit('send', 'Změnit');
	$form->onSuccess[] = [$this, 'changeFormSucceeded'];
	return $form;
}
public function ChangeFormSucceeded(Form $form, \stdClass $data)
{
	$this->userFacade->update($this->getUser()->getId(), $data);
	$this->getUser()->logout();
	$this->flashMessage('Změna byla úspěšná.');
	$this->redirect('Homepage:');

}
}