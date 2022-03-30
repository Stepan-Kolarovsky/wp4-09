<?php
namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;
use App\Model\PostFacade;

final class PostPresenter extends Nette\Application\UI\Presenter
{
	private PostFacade $facade;

	public function __construct(PostFacade $facade)
	{
		$this->facade = $facade;
	}
	public function actionShow(int $postId)
    {
		$post=$this->facade->getPostbyID ($postId);
        if ($post->status=='ARCHIVED'&&!$this->getUser()->isLoggedIn()) {
	        $this->redirect('Sign:in');
			$this->flashMessage('Nemáš právo vidět archived, kámo!');}
    }
	public function handleLike(int $like, int $postId) {
		
	
	}
	public function renderShow(int $postId): void
    {
		$this->facade->addView($postId);
		
		$post=$this->facade->getPostbyID ($postId);
		
		
			
		if (!$post) {
			$this->error('Stránka nebyla nalezena');
		}

		$this->template->post = $post;
		$this->template->comments = $this->facade->getComments($postId);
    }
    protected function createComponentCommentForm(): Form
    {
	$form = new Form; // means Nette\Application\UI\Form

	$form->addText('name', 'Jméno:')
		->setRequired();

	$form->addEmail('email', 'E-mail:');

	$form->addTextArea('content', 'Komentář:')
		->setRequired();

	$form->addSubmit('send', 'Publikovat komentář');

	return $form;
    }
	
	public function commentFormSucceeded(\stdClass $data): void
    {
	$postId = $this->getParameter('postId');
    $this->facade->addComment($postId, $data);
	$this->flashMessage('Děkuji za komentář', 'success');
	$this->redirect('this');
    }
	
	
}
