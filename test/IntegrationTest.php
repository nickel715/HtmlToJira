<?php

use HtmlConverter\Converter;
use HtmlConverter\Jira;

class IntegrationTest extends PHPUnit_Framework_TestCase
{
    public function testFile()
    {
        $html = file_get_contents(__DIR__.'/example.html');
        $expected = file_get_contents(__DIR__.'/expected.jira');
        $converter = new Converter(new Jira);
        $this->assertEquals($expected, $converter->convert($html));
    }
}
