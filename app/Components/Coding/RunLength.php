<?php


namespace App\Components\Coding;


use Nette\Utils\ArrayHash;
use Tracy\Debugger;

final class RunLength
{
    const ENCODE_PATTERN = '/(.)\\1+/';
    const DECODE_PATTERN = '/(\d+)([^0-9])/';
    const FIRST_ELEMENT = 0;
    const SECOND_ELEMENT = 1;
    const THIRD_ELEMENT = 2;

    /**
     * @var
     */
    private $originalString;

    /**
     * @var string
     */
    private $finalMessage = "";

    /**
     * @var ArrayHash
     */
    private $analysisData;

    /**
     * Huffman constructor.
     * @param $string
     */
    public function __construct($string)
    {
        $this->originalString = $string;
        $this->analysisData = ArrayHash::from(array());
    }

    /**
     * @return string
     */
    public function getFinalMessage(): string
    {
        return $this->finalMessage;
    }

    /**
     * @return ArrayHash
     */
    public function getAnalysisData(): ArrayHash
    {
        return $this->analysisData;
    }

    public function encode() :void {
        $this->prepareEncode();
        $this->countAnalysisData(1);
    }

    public function decode($decode)
    {
        $this->originalString = $decode;
        $this->prepareDecode();
        $this->countAnalysisData(0);
    }

    /**
     * Funkce pro kodovani pomoci regularniho vyrazu
     */
    private function prepareEncode() :void{
        $this->finalMessage = preg_replace_callback(
            self::ENCODE_PATTERN, function ($matches) {
            return strlen($matches[self::FIRST_ELEMENT]).$matches[self::SECOND_ELEMENT];
        },
            $this->originalString
        );
    }

    /**
     * Funkce pro dekodovani pomoci regularniho vyrazu
     */
    private function prepareDecode() :void{
        $this->finalMessage = preg_replace_callback(
            self::DECODE_PATTERN,
            function ($matches) {
                return str_repeat($matches[self::THIRD_ELEMENT], $matches[self::SECOND_ELEMENT]);
            },
            $this->originalString
        );
    }


    /**
     * Funkce pro vytvoreni statistiky pro kodovaci/dekodovaci funkci
     * bool = 1 -> encode
     * bool = 0 -> decode
     */
    private function countAnalysisData($bool) :void{
        if($bool){
            $this->analysisData->encode = [
                "text" => $this->finalMessage,
                "size" => strlen($this->finalMessage)
            ];
        }else {
            $this->analysisData->decode = [
                "text" => $this->finalMessage,
                "size" => strlen($this->finalMessage)
            ];
            $this->analysisData->procent = round(($this->analysisData->encode["size"] / $this->analysisData->decode["size"]) * 100);
            $this->analysisData->pomer = ($this->analysisData->encode["size"] / $this->analysisData->decode["size"]);
            $this->analysisData->zisk = 1 - $this->analysisData->pomer;
        }
    }
}