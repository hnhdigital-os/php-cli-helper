<?php

namespace HnhDigital\CliHelper;

trait OutputTrait
{
    /**
     * Print a big output.
     *
     * @param string $method
     * @param string $text
     *
     * @return void
     */
    protected function bigPrintLine($method, $text)
    {
        $text_length = strlen($text) + 4;

        $this->line('');
        $this->$method(str_repeat(' ', $text_length));
        $this->$method('  '.$text.'  ');
        $this->$method(str_repeat(' ', $text_length));
        $this->line('');
    }

    /**
     * Output a big line.
     *
     * @param string $text
     *
     * @return void
     */
    protected function bigLine($text)
    {
        $this->bigPrintLine('line', $text);
    }

    /**
     * Output a big info.
     *
     * @param string $text
     *
     * @return void
     */
    protected function bigInfo($text)
    {
        $this->bigPrintLine('info', $text);
    }

    /**
     * Output a big error.
     *
     * @param string $text
     *
     * @return void
     */
    protected function bigError($text)
    {
        $this->bigPrintLine('error', $text);
    }
}
