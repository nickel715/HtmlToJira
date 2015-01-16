<?php

namespace HtmlToJira;

use \DOMNode;

/**
 * Interface for destination languages
 *
 * @author Nicolas Hohm <nickel7152@gmail.com>
 */
interface Language {
    public function handleNode(DOMNode $node);
}
