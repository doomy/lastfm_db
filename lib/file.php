<?php
class File {

// version 5
// required: PHP8+

    public function __construct(private string $name, private CurlFetcher $curlFetcher) {
    }

    public function set_name(string $name): void
    {
        $this->name = $name;
    }
    
    public function get_name(): string
    {
        return $this->name;
    }
    
    public function put_contents(string $data): void
    {
        file_put_contents($this->name, $data, FILE_APPEND);
    }
    
    public function get_contents(): string
    {
        return $this->curlFetcher->readUrl($this->name);
        //return file_get_contents($this->name);
    }

    /**
     * @return string[]
     */
    public function get_trimmed_lines(): array
    {
        return $this->_trim_lines(explode("\n", $this->get_contents()));
    }

    /**
     * @param string[] $untrimmed_lines
     * @return string[]
     */
    function _trim_lines(array $untrimmed_lines) {
        $trimmed_lines = [];

        foreach ($untrimmed_lines as $untrimmed_line) {
            $trimmed_lines[] = trim($untrimmed_line);
        }
        return $trimmed_lines;
    }
}
?>
