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
		$post = $this->facade->getPostbyID($postId);
		bdump($post);
		bdump($postId);
		if ($post->status == 'ARCHIVED' && !$this->getUser()->isLoggedIn()) {
			$this->redirect('Sign:in');
			$this->flashMessage('Nemáš právo vidět archived, kámo!');
		}
	}
	public function handleLike(int $postId, int $like )
	{
		$userId = $this->getUser()->getId();
		$this->facade->updateRating($userId, $postId, $like);
	}
	public function renderShow(int $postId): void
	{
		bdump($this->getUser());
		$this->facade->addView($postId);

		$post = $this->facade->getPostbyID($postId);
        $like = $this->facade->getUserRating($postId, $this->getUser()->getId());


		if (!$post) {
			$this->error('Stránka nebyla nalezena');
		}
        $this->template->like = $like;
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
