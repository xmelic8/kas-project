<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Components\Coding\Huffman;
use App\Components\Forms\InputTextForm;
use Nette;
use Nette\Application\UI\Form;
use Tracy\Debugger;


final class HomepagePresenter extends Nette\Application\UI\Presenter
{
    /**
     * @var InputTextForm @inject
     */
    public $inputTextForm;

    public function __construct()
    {

    }

    //------------------------------------------------------------------------------------------------------------------
    //RENDER
    public function renderDefault(){
        if($this->isAjax()){
            $this->template->showBody = true;
            $this->template->huffmanData = $this->inputTextForm->getFinalAnalysis();

            $this->redrawControl();
        }else{
            $this->template->showBody = false;
        }
    }

    //------------------------------------------------------------------------------------------------------------------
    //RENDER

    //------------------------------------------------------------------------------------------------------------------
    //COMPONENT

    /**
     * @return Nette\Application\UI\Form
     */
    public function createComponentInputTextForm(){
        return $this->inputTextForm->create();
    }
}
