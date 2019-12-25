<?php

namespace App\Components\Coding;

use Nette\Utils\ArrayHash;
use Tracy\Debugger;

final class LZW
{
    const SIZE_TRANSLATE_TABLE = 256;
    const EMPTY_STRING = "";
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

    public function encode() :void{
        $this->createTranslationTable();
        $this->processString();
        $this->countAnalysisDataFromEncode();
    }

    public function decode() :void{

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
    private function processString() :void{
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
     * Funkce pro vytvoreni statistiky pro dekodovaci funkci
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

        ksort($tmpArray);
        $this->analysisData->translationTable = $tmpArray;
    }

    /*function compress($unc) {
        $i;$c;$wc;
        $w = "";
        $dictionary = array();
        $result = array();
        $dictSize = 256;
        for ($i = 0; $i < 256; $i += 1) {
            $dictionary[chr($i)] = $i;
        }Debugger::barDump($dictionary);
        for ($i = 0; $i < strlen($unc); $i++) {
            $c = $unc[$i];
            $wc = $w.$c;
            if (array_key_exists($wc, $dictionary)) {
                $w = $wc;
            } else {
                array_push($result,$dictionary[$w]);
                $dictionary[$wc] = $dictSize++;
                $w = (string)$c;
            }
        }Debugger::barDump($result, "RESI OLD");
        if ($w !== "") {
            array_push($result,$dictionary[$w]);
        }
        return implode(",",$result);
    }*/

    function decompress($com) {
        $com = explode(",",$com);
        $i;$w;$k;$result;
        $dictionary = array();
        $entry = "";
        $dictSize = 256;
        for ($i = 0; $i < 256; $i++) {
            $dictionary[$i] = chr($i);
        }
        $w = chr($com[0]);
        $result = $w;
        for ($i = 1; $i < count($com);$i++) {
            $k = $com[$i];
            if ($dictionary[$k]) {
                $entry = $dictionary[$k];
            } else {
                if ($k === $dictSize) {
                    $entry = $w.$w[0];
                } else {
                    return null;
                }
            }
            $result .= $entry;
            $dictionary[$dictSize++] = $w . $entry[0];
            $w = $entry;
        }
        return $result;
    }
}