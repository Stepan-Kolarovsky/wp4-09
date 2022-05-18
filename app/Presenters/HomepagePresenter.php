<?php

declare(strict_types=1);
namespace App\Presenters;

use App\Model\PostFacade;
use App\Model\UserFacade;
use Nette;

final class HomepagePresenter extends Nette\Application\UI\Presenter
{
	private PostFacade $facade;
	private UserFacade $userFacade;

	public function __construct(PostFacade $facade, UserFacade $userFacade)
	{
		$this->facade = $facade;
		$this->userFacade = $userFacade;
	}
	
	public function handleShowRandomNumber(){
       $this->template->number = rand(1,100);
	   $this->redrawControl('randomNumber');
	}
	public function renderDefault(): void
	{
		$this->template->refreshNumber = rand(1,55);
		$this->template->posts = $this->facade
			->getPublicArticles()
			->limit(5);
	}
}