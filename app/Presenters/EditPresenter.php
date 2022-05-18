<?php
namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;
use App\Model\PostFacade;
use Nette\Utils\Random;

final class EditPresenter extends Nette\Application\UI\Presenter
{
	private PostFacade $facade;

	public function __construct(PostFacade $facade)
	{
		$this->facade = $facade;
	}
    protected function createComponentPostForm(): Form
{
	$form = new Form;
	$form->addText('title', 'Titulek:')
		->setRequired();
	$form->addTextArea('content', 'Obsah:')
		->setRequired();
	$form->addUpload('image', 'Soubor')
        ->addRule(Form::IMAGE, 'Thumbnail must be JPEG, PNG or GIF');
    $statuses = [
		'OPEN' => 'OTEVŘENÝ',
		'CLOSED' => 'UZAVŘENÝ',
		'ARCHIVED' => 'Archivovaný',
 		
	];
	
		$form->addSelect('status','Stav', $statuses)
	     ->setDefaultValue('OPEN');
	$categories = $this->facade->getCategories();
	$form->addSelect('category_id','kategorie', $categories);
	$form->addSubmit('send', 'Uložit a publikovat');
	$form->onSuccess[] = [$this, 'postFormSucceeded'];

	return $form;
}
public function postFormSucceeded($form, $data): void
{
	$postId = $this->getParameter('postId');
      if (isset($data->image->size) )
		if ($data->image->isOK()){
			$data->image->move('upload/' . $data->image->getSanitizedName());
			$data['image'] = ('upload/' . $data->image->getSanitizedName());
		}else{
			$data['image'] = NULL; 
		}
    
	if ($postId) {
		$post=$this->facade->editPost($postId, (array) $data);
	} else {
		$post=$this->facade->insertPost((array) $data);
	}

	$this->flashMessage('Příspěvek byl úspěšně publikován.', 'success');
	$this->redirect('Post:show', $post->id);
}
public function handleDeleteImage(int $postId)
{
	$data['image'] = null;
	$post=$this->facade->editPost($postId, (array) $data);
    $this->redrawControl('imageSnippet');
}


public function renderEdit(int $postId): void
{
	$post=$this->facade->getPostById($postId);
	$this->template->post = $post;
	if (!$post) {
		$this->error('Post not found');
	}

	$this->getComponent('postForm')
		->setDefaults($post->toArray());

	$this->template->post = $post;
	
}
public function startup(): void
{
	parent::startup();

	if (!$this->getUser()->isLoggedIn()) {
		$this->redirect('Sign:in');
	}
}

}
