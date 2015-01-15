<?php

namespace HtmlToJira;

use \DOMDocument;
use \DOMNode;

/**
 * Converts html to given destination language
 * 
 * @author Nicolas Hohm <nickel7152@gmail.com>
 */
class HtmlConverter
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
        $row = '';

        if ($node->nodeType === XML_ELEMENT_NODE) {

            $method = 'node' . ucfirst(strtolower($node->nodeName));

            if (method_exists($this->language, $method)) {
                $row = $this->language->$method($node);
            } else {
                $row = $this->language->nodeDefault($node);
            }

        } elseif ($node->nodeType === XML_TEXT_NODE) {
            $row = $node->nodeValue;
        }

        $this->result .= $row;
    }
}
