<?php

namespace Nukeflame\Webmatics;

class Analyzer
{
    public function greet(string $name): string
    {
        logger()->debug('package initilized');

        return "Hello, {$name}!";
    }
}
