<?php

namespace App\Components\Forms;

use App\Components\Coding\Huffman;
use Nette\Application\UI\Form;
use SplPriorityQueue;
use Tracy\Debugger;

final class InputTextForm extends FormFactory
{
    public $finalAnalysis;

    public function create():Form
    {
        parent::create();

        $this->form->addText("text")
            ->setRequired("Zadejte prosím textovou hodnotu");

        return $this->form;
    }

    public function formSubmitted(Form $form, \stdClass $values){
        Debugger::barDump($values);

        if($values->text == ""){
            return;
        }

        $huffmanModel = new Huffman($values->text);
        $huffmanModel->encode();
        $final = $huffmanModel->getFinalMessage();

        $huffmanModel->decode($final);
        $final = $huffmanModel->getFinalMessage();

        $this->finalAnalysis = $huffmanModel->getAnalysisData();
    }

    /**
     * @return mixed
     */
    public function getFinalAnalysis()
    {
        return $this->finalAnalysis;
    }
}