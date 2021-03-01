<?php
namespace Ohryan\Humanstxt;

class Parser {

    const SECTION_PATTERN = '\/\*\s?(TEAM|THANKS|SITE)\s?\*\/';

    private $fp;

    public array $parsed = [];

    /**
     * Parser constructor.
     */
    public function __construct(string $file)
    {
        $this->fp = fopen($file, 'r');
        $this->parse();
        return $this;
    }

    /**
     * Parse the humans.txt file.
     */
    public function parse(): void
    {
        $counter = 0;
        $current_section = null;

        while(!feof($this->fp)) {
            $line = $this->nextLine();
            
            // new section
            if ($section = $this->startSection($line)) {
                $counter = 0;
                $current_section = $section;
                continue;
            }

            while($current_section != null && !empty($line)) {
                $this->parsed[$current_section][$counter][] = $this->parseLine($line);
                $line = $this->nextLine();
            }

            $counter++;
        }
    }

    /**
     * Match the start of a new section.
     * 
     * @return string|false the name of the section or false if the string is not a new section.
     */
    private function startSection(string $line)
    {
        return preg_match('/' . self::SECTION_PATTERN . '/i', $line, $matches) ? strtolower($matches[1]) : false;
    }

    /**
     * Get and return the next line.
     * 
     * @return string the trimmed line.
     */
    private function nextLine(): ?string
    {
        return trim(fgets($this->fp));
    }

    /**
     * Convert the line to an associative array.
     * 
     * @return array the parsed line.
     */
    private function parseLine(string $line): array
    {
        list($k, $v) = array_map('trim', explode(':', $line));
        return [$k => $v];
    }

    /**
     * Convience method to return JSON.
     * 
     * @return string JSON string.
     */
    public function toJson(): string
    {
        return json_encode($this->parsed);
    }

    /**
     * Convience method to return array.
     * 
     * @return array the array.
     */
    public function toArray(): array
    {
        return $this->parsed;
    }
}
