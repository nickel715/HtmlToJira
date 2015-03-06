<?php

namespace HtmlConverter\Test;

use HtmlConverter\Jira;
use \DOMDocument;
use \DOMNode;
use \DOMText;
use \DOMElement;

class JiraTest extends \PHPUnit_Framework_TestCase
{
    private $sut;
    private $dom;
    private $nodeValue = 'hallo welt';

    public function setUp()
    {
        $this->sut = new Jira;
        $this->dom = new DOMDocument;
    }

    public function testNodeDefault()
    {
        $node = new DOMText($this->nodeValue);
        $this->assertEquals($this->nodeValue, $this->sut->nodeDefault($node));
    }

    public function testNodePWithTextOnly()
    {
        $node = new DOMElement('p', $this->nodeValue);
        $this->assertEquals($this->nodeValue . PHP_EOL . PHP_EOL, $this->sut->nodeP($node));
    }

    public function testNodePWithLink()
    {
        $nodeP = $this->dom->createElement('p');
        $nodeA = $this->dom->createElement('a', 'example.com');
        $nodeA->setAttribute('href', 'http://example.com');
        $nodeP->appendChild($nodeA);

        $expected = '[example.com|http://example.com]' . PHP_EOL . PHP_EOL;
        $this->assertEquals($expected, $this->sut->nodeP($nodeP));
    }

    public function testNodePWithLinkAndText()
    {
        $nodeP = $this->dom->createElement('p', $this->nodeValue);
        $nodeA = $this->dom->createElement('a', 'example.com');
        $nodeA->setAttribute('href', 'http://example.com');
        $nodeP->appendChild($nodeA);

        $expectedHtml = '<p>' . $this->nodeValue;
        $expectedHtml .= '<a href="http://example.com">example.com</a></p>';
        $this->assertEquals($expectedHtml, $this->dom->saveHtml($nodeP));

        $expected = $this->nodeValue.'[example.com|http://example.com]' . PHP_EOL . PHP_EOL;
        $this->assertEquals($expected, $this->sut->nodeP($nodeP));
    }

    public function testNodePWithMultipleLink()
    {
        $nodeP = $this->dom->createElement('p');
        $nodeA = $this->dom->createElement('a', 'example.com');
        $nodeA2 = $this->dom->createElement('a', 'example2.com');
        $nodeA->setAttribute('href', 'http://example.com');
        $nodeA2->setAttribute('href', 'http://example2.com');
        $nodeP->appendChild($nodeA);
        $nodeP->appendChild($nodeA2);

        $expected = '[example.com|http://example.com][example2.com|http://example2.com]' . PHP_EOL . PHP_EOL;
        $this->assertEquals($expected, $this->sut->nodeP($nodeP));
    }

    private function headingNodes($level)
    {
        $node = new DOMElement('h' . $level, $this->nodeValue);
        $expected = 'h' . $level . '. ' . $this->nodeValue . PHP_EOL;
        $this->assertEquals($expected, $this->sut->{'nodeH' . $level}($node));
    }

    public function testNodeH1()
    {
        $this->headingNodes(1);
    }

    public function testNodeH2()
    {
        $this->headingNodes(2);
    }

    public function testNodeH3()
    {
        $this->headingNodes(3);
    }

    public function testNodeH4()
    {
        $this->headingNodes(4);
    }

    public function testNodeH5()
    {
        $this->headingNodes(5);
    }

    public function testNodeH6()
    {
        $this->headingNodes(6);
    }

    public function testNodeB()
    {
        $node = new DOMElement('b', $this->nodeValue);
        $expected = '*' . $this->nodeValue . '*';
        $this->assertEquals($expected, $this->sut->nodeB($node));
    }

    public function testNodeStrong()
    {
        $node = new DOMElement('b', $this->nodeValue);
        $expected = '*' . $this->nodeValue . '*';
        $this->assertEquals($expected, $this->sut->nodeStrong($node));
    }

    public function testNodeHr()
    {
        $node = new DOMElement('hr');
        $this->assertEquals('----' . PHP_EOL, $this->sut->nodeHr($node));
    }

    public function testNodeA()
    {
        $node = $this->dom->createElement('a', 'example.com');
        $node->setAttribute('href', 'http://example.com');
        $expected = '[example.com|http://example.com]';
        $this->assertEquals($expected, $this->sut->nodeA($node));
    }

    public function testNodeUlEmpty()
    {
        $node = new DOMElement('ul');
        $this->assertEquals('', $this->sut->nodeUl($node));
    }

    public function testNodeUlWithLi()
    {
        $nodeUl = $this->dom->createElement('ul');
        $nodeLi = $this->dom->createElement('li', $this->nodeValue);
        $nodeUl->appendChild($nodeLi);
        $expected = '* ' . $this->nodeValue . PHP_EOL . PHP_EOL;
        $this->assertEquals($expected, $this->sut->nodeUl($nodeUl));
    }

    public function testNodeUlWithMultipleLi()
    {
        $nodeUl = $this->dom->createElement('ul');
        $nodeLi1 = $this->dom->createElement('li', $this->nodeValue . '1');
        $nodeLi2 = $this->dom->createElement('li', $this->nodeValue . '2');
        $nodeUl->appendChild($nodeLi1);
        $nodeUl->appendChild($nodeLi2);
        $expected = '* ' . $this->nodeValue . '1' . PHP_EOL;
        $expected .= '* ' . $this->nodeValue . '2' . PHP_EOL;
        $expected .= PHP_EOL;
        $this->assertEquals($expected, $this->sut->nodeUl($nodeUl));
    }


    public function testNodeUlWithNewlineBetweetLi()
    {
        $nodeUl = $this->dom->createElement('ul');
        $nodeLi1 = $this->dom->createElement('li', $this->nodeValue . '1');
        $nodeLi2 = $this->dom->createElement('li', $this->nodeValue . '2');
        $nodeLf = new DOMText(PHP_EOL);
        $nodeUl->appendChild($nodeLi1);
        $nodeUl->appendChild($nodeLf);
        $nodeUl->appendChild($nodeLi2);

        $expected = '* ' . $this->nodeValue . '1' . PHP_EOL;
        $expected .= '* ' . $this->nodeValue . '2' . PHP_EOL;
        $expected .= PHP_EOL;
        $this->assertEquals($expected, $this->sut->nodeUl($nodeUl));
    }

    public function testNodePreWithoutLanguage()
    {
        $code = $this->nodeValue . PHP_EOL . $this->nodeValue;
        $nodeCode = $this->dom->createElement('code', $code);
        $nodePre = $this->dom->createElement('pre');
        $nodePre->appendChild($nodeCode);

        $expectedHtml = '<pre><code>' . $code . '</code></pre>';
        $this->assertEquals($expectedHtml, $this->dom->saveHtml($nodePre));

        $expected = '{code}' . PHP_EOL . $code . PHP_EOL . '{code}' . PHP_EOL;
        $this->assertEquals($expected, $this->sut->nodePre($nodePre));
    }

    public function testNodePreWithoutContent()
    {
        $node = new DOMElement('pre');
        $this->assertEquals('{code}' . PHP_EOL . PHP_EOL . '{code}' . PHP_EOL, $this->sut->nodePre($node));
    }

    public function testNodePreWithLanguage()
    {
        $code = $this->nodeValue . PHP_EOL . $this->nodeValue;
        $nodeCode = $this->dom->createElement('code', $code);
        $nodeCode->setAttribute('class', 'language-php');
        $nodePre = $this->dom->createElement('pre');
        $nodePre->appendChild($nodeCode);

        $expectedHtml = '<pre><code class="language-php">' . $code . '</code></pre>';
        $this->assertEquals($expectedHtml, $this->dom->saveHtml($nodePre));

        $expected = '{code:php}' . PHP_EOL . $code . PHP_EOL . '{code}' . PHP_EOL;
        $this->assertEquals($expected, $this->sut->nodePre($nodePre));
    }

    public function testNodeCode()
    {
        $node = new DOMElement('code', $this->nodeValue);
        $this->assertEquals('{{'. $this->nodeValue .'}}', $this->sut->nodeCode($node));
    }

    public function testNodeOlWithLi()
    {
        $nodeOl = $this->dom->createElement('ol');
        $nodeLi = $this->dom->createElement('li', $this->nodeValue);
        $nodeOl->appendChild($nodeLi);
        $expected = '# ' . $this->nodeValue . PHP_EOL . PHP_EOL;
        $this->assertEquals($expected, $this->sut->nodeOl($nodeOl));
    }

    public function testNestedList()
    {
        $nodeUlLevel1 = $this->dom->createElement('ul');
        $nodeLiLevel1 = $this->dom->createElement('li', $this->nodeValue);
        $nodeUlLevel2 = $this->dom->createElement('ul');
        $nodeLiLevel2 = $this->dom->createElement('li', $this->nodeValue);

        $nodeUlLevel1->appendChild($nodeLiLevel1);
        $nodeUlLevel2->appendChild($nodeLiLevel2);
        $nodeLiLevel1->appendChild($nodeUlLevel2);

        $expectedHtml = '<ul><li>' . $this->nodeValue . '<ul><li>' . $this->nodeValue . '</li></ul></li></ul>';
        $actual = str_replace(array("\n", "\r"), '', $this->dom->saveHtml($nodeUlLevel1));

        $this->assertEquals($expectedHtml, $actual);

        $expected = '* ' . $this->nodeValue . PHP_EOL . '** ' . $this->nodeValue . PHP_EOL . PHP_EOL;
        $this->assertEquals($expected, $this->sut->nodeUl($nodeUlLevel1));
    }

    public function testDeppNestedList()
    {
        $nodeUlLevel1 = $this->dom->createElement('ul');
        $nodeLiLevel1 = $this->dom->createElement('li', $this->nodeValue);
        $nodeUlLevel2 = $this->dom->createElement('ul');
        $nodeLiLevel2 = $this->dom->createElement('li', $this->nodeValue);
        $nodeUlLevel3 = $this->dom->createElement('ul');
        $nodeLiLevel3 = $this->dom->createElement('li', $this->nodeValue);

        $nodeUlLevel1->appendChild($nodeLiLevel1);
        $nodeLiLevel1->appendChild($nodeUlLevel2);
        $nodeUlLevel2->appendChild($nodeLiLevel2);
        $nodeLiLevel2->appendChild($nodeUlLevel3);
        $nodeUlLevel3->appendChild($nodeLiLevel3);

        $expected = sprintf(
            '* %s%s** %s%s*** %s%s%s',
            $this->nodeValue,
            PHP_EOL,
            $this->nodeValue,
            PHP_EOL,
            $this->nodeValue,
            PHP_EOL,
            PHP_EOL
        );
        $this->assertEquals($expected, $this->sut->nodeUl($nodeUlLevel1));
    }
}
