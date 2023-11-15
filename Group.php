<?php

class Group
{
    /**
     * @var int
     */
    protected static $event_limit = 50;
    /**
     * @var string
     */
    protected static $event_type;
    /**
     * @var \DOMDocument
     */
    protected static $dom;
    /**
     * @var int
     */
    protected static $tpInsc;
    /**
     * @var string
     */
    protected static $nrInsc;

    /**
     * Reagrupa os eventos originais gerados pelo sped-efdreinf em uma nova estrutura de xml
     * o agrupamento requer:
     *  - o mesmo tipo de evento
     *  - o mesmo contribuinte
     *  - a estrutura irá basear a montagem e as regras no item[0] do array passado como parâmetro
     * @param array $events
     * @return void
     */
    public static function addXmlEvents(array $events)
    {
        $xml = $events[0];
        self::init($events[0]);
        self::checkLimit($events);
        $root = self::$dom->getElementsByTagName('eventos')->item(0);
        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput = false;
        foreach($events as $event) {
            $doc->loadXML($event);
            $n = $doc->getElementsByTagName(self::$event_type)->count();
            $i = 0;
            while($i < $n) {
                $node  = $doc->getElementsByTagName(self::$event_type)->item($i);
                if (!self::checkNrInsc($doc->saveXML($node))) {
                    continue;
                }
                $evt = self::$dom->createElement('evento');
                $att = self::$dom->createAttribute('id');
                $att->value = "ID";
                $evt->appendChild($att);
                $new = self::$dom->importNode($node, true);
                $evt->appendChild($new);
                $root->appendChild($evt);
                $i++;
            }
        }
        return self::$dom->saveXML();
    }

    /**
     * Verifica se o limite imposto de 50 eventos não foi ultrapassado
     * @param $events
     * @return void
     */
    protected static function checkLimit(array $events)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;
        $tot = 0;
        foreach($events as $event) {
            $dom->loadXML($event);
            $tot += $dom->getElementsByTagName(self::$event_type)->count();
        }
        if ($tot > self::$event_limit) {
            throw new InvalidArgumentException("Número total de eventos informados é maior que 50");
        }
    }

    /**
     * Verifica se todos os eventos são relativos aos mesmo contribuinte
     * @param $xml
     * @return bool
     */
    protected static function checkNrInsc($xml)
    {
        $sxml = simplexml_load_string($xml);
        $json = str_replace(
            '@attributes',
            'attributes',
            json_encode($sxml, JSON_PRETTY_PRINT)
        );
        $std = json_decode($json);
        if ($std->ideContri->nrInsc !== self::$nrInsc) {
            return false;
        }
        return true;
    }

    /**
     * Coleta os dados iniciais e monta o xml base em DOMDocument
     * @param string $xml
     * @return void
     */
    protected static function init(string $xml)
    {
        self::$event_type = self::getType($xml);
        self::getTagContrib($xml);
        //constroi a estrutura inicial do xml de saída
        $out = "<Reinf xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" "
            . "xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" "
            . "xmlns=\"http://www.reinf.esocial.gov.br/schemas/envioLoteEventosAssincrono/v1_00_00\">"
            . "<envioLoteEventos>"
            . "<ideContribuinte>"
            . "<tpInsc></tpInsc>"
            . "<nrInsc></nrInsc>"
            . "</ideContribuinte>"
            . "<eventos>"
            . "</eventos>"
            . "</envioLoteEventos>"
            ."</Reinf>";
        //carrega a propriedade $dom da classe
        self::$dom = new \DOMDocument('1.0', 'UTF-8');
        self::$dom->preserveWhiteSpace = false;
        self::$dom->formatOutput = true;
        self::$dom->loadXML($out);
        $envia = self::$dom->getElementsByTagName('envioLoteEventos')->item(0);
        self::$dom->getElementsByTagName('tpInsc')->item(0)->nodeValue = self::$tpInsc;
        self::$dom->getElementsByTagName('nrInsc')->item(0)->nodeValue = self::$nrInsc;
    }

    /**
     * Obtem o tipo de evento que será processado
     * @param string $xml
     * @return string|void
     */
    protected static function getType(string $xml): string
    {
        $sxml = simplexml_load_string($xml);
        foreach($sxml->children() as $node) {
            $name = $node->getName();
            return $name;
        }
    }

    /**
     * Obtem os dados do contribuinte
     * @param $xml
     * @return void
     */
    protected static function getTagContrib(string $xml)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;
        $dom->loadXML($xml);
        $contrib = $dom->getElementsByTagName('ideContri')->item(0);
        $tpInsc = $contrib->getElementsByTagName('tpInsc')->item(0)->nodeValue;
        $nrInsc = $contrib->getElementsByTagName('nrInsc')->item(0)->nodeValue;
        self::$tpInsc = $tpInsc;
        self::$nrInsc = substr($nrInsc, 0, 8);
    }
}