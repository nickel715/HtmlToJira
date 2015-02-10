<?php

namespace HtmlConverter;

use \DOMNode;

/**
 * Destination language for Jira syntax
 *
 * @author Nicolas Hohm <nickel7152@gmail.com>
 */
class Jira implements Language
{

    public function handleNode(DOMNode $node) {
        $row = '';
        if ($node->nodeType === XML_ELEMENT_NODE) {

            $method = 'node' . ucfirst(strtolower($node->nodeName));

            if (method_exists($this, $method)) {
                $row = $this->$method($node);
            } else {
                $row = $this->nodeDefault($node);
            }

        } elseif ($node->nodeType === XML_TEXT_NODE && $node->nodeValue != PHP_EOL) {
            $row = str_replace(PHP_EOL, ' ', $node->nodeValue);
        }
        return $row;
    }

    public function nodeDefault(DOMNode $node)
    {
        return $node->nodeValue;
    }

    public function nodeP(DOMNode $node)
    {
        $content = '';
        /** @var DOMNode $childNode */
        foreach ($node->childNodes as $childNode) {
            $content .= $this->handleNode($childNode);
        }
        return trim($content) . PHP_EOL;
    }

    private function heading($level, DOMNode $node)
    {
        return sprintf('h%d. %s%s', $level, $node->nodeValue, PHP_EOL);
    }

    public function nodeH1(DOMNode $node) { return $this->heading(1, $node); }
    public function nodeH2(DOMNode $node) { return $this->heading(2, $node); }
    public function nodeH3(DOMNode $node) { return $this->heading(3, $node); }
    public function nodeH4(DOMNode $node) { return $this->heading(4, $node); }
    public function nodeH5(DOMNode $node) { return $this->heading(5, $node); }
    public function nodeH6(DOMNode $node) { return $this->heading(6, $node); }

    private function surround($surrounding, $value)
    {
        return $surrounding . $value . $surrounding;
    }

    public function nodeB(DOMNode $node) { return $this->surround('*', $node->nodeValue); }
    public function nodeStrong(DOMNode $node) { return $this->surround('*', $node->nodeValue); }
    public function nodeEm(DOMNode $node) { return $this->surround('_', $node->nodeValue); }
    public function nodeCite(DOMNode $node) { return $this->surround('??', $node->nodeValue); }
    public function nodeStrike(DOMNode $node) { return $this->surround('-', $node->nodeValue); }
    public function nodeS(DOMNode $node) { return $this->nodeStrike($node); }
    public function nodeDel(DOMNode $node) { return $this->nodeStrike($node); }
    public function nodeU(DOMNode $node) { return $this->surround('+', $node->nodeValue); }
    public function nodeSup(DOMNode $node) { return $this->surround('^', $node->nodeValue); }
    public function nodeSub(DOMNode $node) { return $this->surround('~', $node->nodeValue); }

    private function tag($name, $content, $head = null)
    {
        if ($head === null) {
            $head = $name;
        }
        return sprintf('{%s}%s%s%s{%s}%s', $head, PHP_EOL, $content, PHP_EOL, $name, PHP_EOL);
    }

    public function nodeBlockquote(DOMNode $node) { return $this->tag('quote', $node->nodeValue); }
    public function nodeHr(DOMNode $node) { return '----' . PHP_EOL; }

    public function nodeA(DOMNode $node) { return sprintf('[%s|%s]', $node->nodeValue, $node->attributes->getNamedItem('href')->nodeValue); }

    private function listNode(DOMNode $node)
    {
        $listItems = [];
        $bullet = '*';

        if ($node->nodeName == 'ol') {
            $bullet = '#';
        }

        /** @var DOMNode $li */
        foreach ($node->childNodes as $li) {
            if ($li->nodeType != XML_TEXT_NODE) { // ignore text between list items
                $listItems[] = sprintf('%s %s', $bullet, $this->listItemNode($li));
            }
        }
        if (count($listItems) > 0) {
            return implode(PHP_EOL, $listItems) . PHP_EOL . PHP_EOL;
        } else {
            return '';
        }
    }

    private function listItemNode(DOMNode $li) {
        $content = '';
        foreach ($li->childNodes as $liChild) {
            $content .= $this->handleNode($liChild);
        }
        return $content;
    }

    public function nodeUl(DOMNode $node) { return $this->listNode($node); }
    public function nodeOl(DOMNode $node) { return $this->listNode($node); }
    public function nodeLi(DOMNode $node) { return ''; }

    public function nodePre(DOMNode $node)
    {
        $head = null;
        if ($node->hasChildNodes()) {
            $codeNode = $node->childNodes->item(0);
            if ($codeNode->hasAttribute('class')) {
                $codeClass = $codeNode->attributes->getNamedItem('class')->nodeValue;
                $codeClass = explode('-', $codeClass);
                if ($codeClass[0] === 'language') {
                    $head = 'code:' . $codeClass[1];
                }
            }
        }
        return $this->tag('code', $node->nodeValue, $head);
    }

    public function nodeCode(DOMNode $node) { return sprintf('{{%s}}', $node->nodeValue); }

    public function node(DOMNode $node) { return $node->nodeValue; }
}
