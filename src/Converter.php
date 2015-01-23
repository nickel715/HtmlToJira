<?php

namespace HtmlConverter;

use \DOMDocument;
use \DOMNode;

/**
 * Converts html to given destination language
 *
 * @author Nicolas Hohm <nickel7152@gmail.com>
 */
class Converter
{
    private $result = '';
    private $language;

    public function __construct(Language $language) {
        $this->language = $language;
    }

    public function convert($html)
    {
        $dom = new DOMDocument;
        $dom->loadHTML($html);
        $elements = $dom->childNodes->item(1)->childNodes->item(0)->childNodes;

        foreach ($elements as $element) {
            $this->appendNode($element);
        }

        return $this->result;
    }

    protected function appendNode(DOMNode $node)
    {
        $this->result .= $this->language->handleNode($node);
    }
}
