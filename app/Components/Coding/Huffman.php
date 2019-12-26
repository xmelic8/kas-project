<?php


namespace App\Components\Coding;


use Nette\Utils\ArrayHash;
use Tracy\Debugger;

final class Huffman
{
    const FIRST_ELEMENT = 0;
    const LEFT_ELEMENT = "count";
    const RIGHT_ELEMENT = "node";

    /**
     * @var
     */
    private $originalString;

    /**
     * @var array
     */
    private $occurences = array();

    /**
     * @var array
     */
    private $translationTable = array();

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
     * @param string $finalMessage
     */
    public function setFinalMessage(string $finalMessage): void
    {
        $this->finalMessage = $this->finalMessage . $finalMessage;
    }

    /**
     * @return ArrayHash
     */
    public function getAnalysisData(): ArrayHash
    {
        return $this->analysisData;
    }

    /**
     * Obsluzna funkce pro zakodovani zpravy
     */
    public function encode() :void{
        $this->countOccurencesSymbol();
        $this->modifyArraySequence();
        $this->generateTranslationTable();
        $this->buildFinalMessage();

        $this->countAnalysisDataFromEncode();
    }

    /**
     * @param $message
     */
    public function decode($message){
        $this->originalString = $message;
        $this->buildDecodeFinalMessages();

        $this->countAnalysisDataFromDecode();
    }

    /**
     * Funkce spocita pocet vyskytu jednotlivych znaku ve vstupnim textu. Vytvori z toho pole a na zaver jej seradi
     * podle cetnosti vyskytu znaku od nejmene se vyskytujiciho po nejcastejsi.
     */
    private function countOccurencesSymbol() :void{
        $tmpString = $this->originalString;

        while (strlen($tmpString)) {
            $this->occurences[] = [
                self::LEFT_ELEMENT => substr_count($tmpString, $tmpString[self::FIRST_ELEMENT]),
                self::RIGHT_ELEMENT => $tmpString[self::FIRST_ELEMENT]
            ];
            $tmpString = str_replace($tmpString[self::FIRST_ELEMENT], '', $tmpString);
        }

        sort($this->occurences);
    }

    /**
     * Funkce z vypocitaneho vyskytu jednotlivych znaku vytvori novou zanorenou posloupnost, ktera
     * odpovida tabulce pravdepodobnosti. Kde count odpovida souctu vyskytu a node jsou podrizene uzly.
     */
    private function modifyArraySequence() :void{
        while (count($this->occurences) > 1) {
            $first = array_shift($this->occurences);
            $secod = array_shift($this->occurences);

            $this->occurences[] = [
                self::LEFT_ELEMENT => $first[self::LEFT_ELEMENT] + $secod[self::LEFT_ELEMENT],
                self::RIGHT_ELEMENT => [
                    self::LEFT_ELEMENT => $first,
                    self::RIGHT_ELEMENT => $secod
                ]
            ];

            sort($this->occurences);
        }
    }

    /**
     * Volani rekurzivni funkce pro nahrazeni znaku za posloupnost bitu.
     */
    private function generateTranslationTable() :void{
        if(is_array($this->occurences[0][self::RIGHT_ELEMENT])){
            $this->recursiveGenerateTranslationTable($this->occurences[0][self::RIGHT_ELEMENT]);
        }else{
            $this->recursiveGenerateTranslationTable($this->occurences);
        }
    }

    /**
     * Funkce pro obsluhu zavolani generovaci funkce, kterou vola pro levy i pravy element uzlu.
     * @param $data
     * @param string $value
     */
    private function recursiveGenerateTranslationTable($data, $value = '') :void{
        $this->processingTranslationTable($data, self::LEFT_ELEMENT, $value);

        if(!isset($data[self::RIGHT_ELEMENT])){
            return;
        }

        $this->processingTranslationTable($data, self::RIGHT_ELEMENT, $value);
    }

    /**
     * Hlavni funkce, ktera se stara o vygenerovani pro kazdy znak bitove poslouposti znaku, do kterych je zakodovana.
     * @param $data
     * @param $element
     * @param string $value
     */
    private function processingTranslationTable($data, $element, $value = '') :void{
        if($element === self::LEFT_ELEMENT){
            $value = $value.'0';
        }else{
            $value = $value.'1';
        }

        if(!isset($data[$element])){
            $this->translationTable[$data[0][self::RIGHT_ELEMENT]] = $value;
        }
        elseif(is_array($data[$element][self::RIGHT_ELEMENT])){
            $this->recursiveGenerateTranslationTable($data[$element][self::RIGHT_ELEMENT], $value);
        }
        else{
            $this->translationTable[$data[$element][self::RIGHT_ELEMENT]] = $value;
        }
    }

    /**
     * Funkce, ktera sestavi vyslednou zpravu
     */
    private function buildFinalMessage() :void {
        for($i = 0; $i < strlen($this->originalString); $i++) {
           $this->setFinalMessage($this->getTranslationSymbol($i));
        }
    }

    /**
     * @param $counter
     * @return mixed
     */
    private function getTranslationSymbol($counter){
        return $this->translationTable[$this->originalString[$counter]];
    }

    /**
     * Funkce slouzi prekladu zakodovaneho textu do citelne formy.
     */
    private function buildDecodeFinalMessages() :void{
        $this->finalMessage = $this->originalString;
        $value = "";

        for($i = 0; $i < strlen($this->originalString); $i++){
            $value = $value . $this->originalString[$i];

            if($char = array_search($value, $this->translationTable, true)){
                $this->finalMessage = preg_replace('/' . $value . '/', $char, $this->finalMessage, 1);
                $value = "";
            }
        }
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
        $this->analysisData->translationTable = $this->translationTable;
        $this->analysisData->procent = round(($this->analysisData->encode["size"] / $this->analysisData->decode["size"])*100);
    }

    /**
     * Funkce pro vytvoreni statistiky pro kodovaci funkci
     */
    private function countAnalysisDataFromEncode() :void{
        $this->analysisData->encode = [
            "text" => $this->finalMessage,
            "size" => strlen($this->finalMessage)
        ];
    }
}