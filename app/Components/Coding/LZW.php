<?php

namespace App\Components\Coding;

use Nette\Utils\ArrayHash;
use Tracy\Debugger;

final class LZW
{
    const SIZE_TRANSLATE_TABLE = 256;
    const EMPTY_STRING = "";
    const FIRST_CHAR = 0;

    /**
     * @var
     */
    private $originalString;

    /**
     * @var string
     */
    private $finalMessage = self::EMPTY_STRING;

    /**
     * @var array
     */
    private $translationTable = [

    ];

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

    /**
     * Hlavní řídící fumkce pro zakodování vstupního řetězce
     */
    public function encode() :void{
        $this->createTranslationTable();
        $this->processEncode();
        $this->countAnalysisDataFromEncode();
    }

    /**
     * @param string $string
     */
    public function decode(string $string) :void{
        $this->originalString = $string;
        $this->processDecode();
        $this->countAnalysisDataFromDecode();
    }

    /**
     * Funkce vygeneruje zakladni prekladovou tabulku
     */
    private function createTranslationTable() :void{
        for($i = 0; $i < self::SIZE_TRANSLATE_TABLE; $i++){
            $this->translationTable[$i] = chr($i);
        }
    }

    /**
     * Funkce provede zakodovani vstupnich dat
     */
    private function processEncode() :void{
        $sizeTranslationTable = self::SIZE_TRANSLATE_TABLE;
        $tmpResult = array();
        $newChar = self::EMPTY_STRING;

        for($i = 0; $i < strlen($this->originalString); $i++){
            $char = $this->originalString[$i];

            if (in_array($newChar.$char, $this->translationTable)) {
                $newChar = $newChar.$char;
                continue;
            }

            $tmpResult[] = array_search($newChar, $this->translationTable, 1);
            $this->translationTable[$sizeTranslationTable++] = $newChar.$char;
            $newChar = $char;
        }

        if ($newChar !== self::EMPTY_STRING) {
            $tmpResult[] = array_search($newChar, $this->translationTable, 1);
        }

        $this->createFinalMessage($tmpResult);
    }

    /**
     * @param array $array
     */
    private function createFinalMessage(array $array) :void{
        $this->finalMessage = implode(",",$array);
    }

    /**
     * Funkce pro dekodovani zakodovaneho textu.
     * Funkce pocita s jiz vytvorenou prekladovou tabulkou.
     */
    private function processDecode() :void{
        $inputArray = explode(",", $this->originalString);
        $sizeTranslationTable = self::SIZE_TRANSLATE_TABLE;

        $newChar = $this->translationTable[reset($inputArray)];
        $message = $newChar;

        for ($i = 1; $i < count($inputArray); $i++) {
            $char = $inputArray[$i];
            if ($this->translationTable[$char]) {
                $tmpChar = $this->translationTable[$char];
            }elseif(intval($char) === $sizeTranslationTable) {
                $tmpChar = $newChar . $newChar[self::FIRST_CHAR];
            }else{
                return;
            }

            $this->translationTable[$sizeTranslationTable++] = $newChar . $tmpChar[self::FIRST_CHAR];
            $message = $message . $tmpChar;
            $newChar = $tmpChar;
        }

        $this->finalMessage = $message;
    }

    /**
     * Funkce pro vytvoreni statistiky pro kodovaci funkci
     */
    private function countAnalysisDataFromEncode() :void{
        $tmpArraySource = explode(",",$this->finalMessage);

        $this->analysisData->encode = [
            "text" => $this->finalMessage,
            "size" => PHP_INT_SIZE * count($tmpArraySource)
        ];

        $tmpArray = array();
        foreach ($tmpArraySource as  $value){
            $tmpArray[$this->translationTable[$value]] = $value;
        }

        asort($tmpArray);
        $this->analysisData->translationTable = $tmpArray;
    }

    /**
     * Funkce pro vytvoreni statistiky pro dekodovaci funkci
     */
    private function countAnalysisDataFromDecode() :void{
        $charsArray = str_split($this->finalMessage);

        $binary = "";
        foreach ($charsArray as $character) {
            $data = unpack('H*', $character);
            $binary = $binary . base_convert($data[1], 16, 2);
        }

        $this->analysisData->decode = [
            "text" => $this->finalMessage,
            "size" => strlen($binary)
        ];

        $this->analysisData->procent = round(($this->analysisData->encode["size"] / $this->analysisData->decode["size"])*100);
    }
}