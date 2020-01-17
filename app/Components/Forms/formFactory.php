<?php

namespace App\Components\Forms;

use Nette;
use Nette\Application\UI\Form;
use Nette\Utils\Strings;
use Tracy\Debugger;


class FormFactory extends Nette\Application\UI\Control
{
    use Nette\SmartObject;

    /**
     * @var Nette\Application\UI\Form
     */
    protected $form;

    /**
     * @return Form
     */
    public function create() :Form
    {
        $this->form = new Form;

        $this->form->addProtection('Vypršel časový limit, odešlete formulář znovu');

        $this->form->addSubmit('submit', '');

        $this->form->getElementPrototype()->class('ajax');

        $this->form->onSuccess[] = [$this, 'formSubmitted'];

        return $this->form;
    }

    public function formSubmitted(Form $form, \stdClass $values){

    }

    /**
     * @param string $string
     * @return string
     */
    static function removeDiacritics(string $string) :string {
        return Strings::toAscii($string);
    }

    /**
     * @param string $string
     * @return string
     */
    static function removeBadChar(string $string) :string {
        return preg_replace('/[0-9]+/', '', $string);
    }
}
