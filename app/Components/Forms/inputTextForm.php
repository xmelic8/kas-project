<?php

namespace App\Components\Forms;

use App\Components\Coding\Huffman;
use App\Components\Coding\LZW;
use App\Components\Coding\RunLength;
use Nette\Application\UI\Form;
use SplPriorityQueue;
use Tracy\Debugger;

final class InputTextForm extends FormFactory
{
    /**
     * @var
     */
    private $finalAnalysisHuffman;

    /**
     * @var
     */
    private $finalAnalysisRunLength;

    /**
     * @var
     */
    private $finalAnalysisLZW;

    public function create(): Form
    {
        parent::create();

        $this->form->addTextArea("text")
            ->setRequired("Zadejte prosím textovou hodnotu");

        return $this->form;
    }

    public function formSubmitted(Form $form, \stdClass $values)
    {
        Debugger::barDump($values);

        if ($values->text == "") {
            return;
        }

        //Huffmanovo kodovani
        $huffmanModel = new Huffman($values->text);
        $huffmanModel->encode();
        $final = $huffmanModel->getFinalMessage();

        $huffmanModel->decode($final);
        $this->finalAnalysisHuffman = $huffmanModel->getAnalysisData();

        //Run Length kodovani
        $runLength = new RunLength($values->text);
        $runLength->encode();
        $final = $runLength->getFinalMessage();

        $runLength->decode($final);
        $this->finalAnalysisRunLength = $runLength->getAnalysisData();

        //LZW kodovani
        $lzw = new LZW($values->text);
        $lzw->encode();
        $final = $lzw->getFinalMessage();

        $lzw->decode($final);
        $this->finalAnalysisLZW = $lzw->getAnalysisData();
    }

    /**
     * @return mixed
     */
    public function getFinalAnalysisHuffman()
    {
        return $this->finalAnalysisHuffman;
    }

    /**
     * @return mixed
     */
    public function getFinalAnalysisRunLength()
    {
        return $this->finalAnalysisRunLength;
    }

    /**
     * @return mixed
     */
    public function getFinalAnalysisLZW()
    {
        return $this->finalAnalysisLZW;
    }
}