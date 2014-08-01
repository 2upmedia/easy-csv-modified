<?php

namespace EasyCSV;

class Reader extends AbstractBase
{
    private $headersInFirstRow = true;
    private $headers = false;
    private $line;
    private $init;

    public function __construct($path, $mode = 'r+', $headersInFirstRow = true)
    {
        parent::__construct($path, $mode);
        $this->headersInFirstRow = $headersInFirstRow;
        $this->line = -1;
    }

    public function getHeaders()
    {
        $this->init();
        return $this->headers;
    }
    
    public function getRow()
    {
        $this->init();

        $this->handle->current(); // jerry-rig to get valid() to return the value correctly

        if ($this->handle->valid() === false) {
            return false;
        }

        if (($row = str_getcsv( $this->handle->current(), $this->delimiter, $this->enclosure )) !== false && $row != null) {
            $this->line++;
            $this->handle->next();
            return $this->headers ? array_combine($this->headers, $row) : $row;
        } else {
            return false;
        }
    }

    public function getAll()
    {
        $data = array();
        while ($row = $this->getRow()) {
            $data[] = $row;
        }
        return $data;
    }

    public function getLineNumber()
    {
        return $this->line;
    }

    /**
     * @param $lineNumber zero-based index
     */
    public function advanceTo( $lineNumber )
    {
        $this->line = $lineNumber;

        $this->handle->seek( $lineNumber );
    }

    /**
     * @param $lineNumber zero-based index
     */
    public function setHeaderLine( $lineNumber )
    {
        if( $lineNumber !== 0 ) {
            $this->headersInFirstRow = false;
        } else {
            return false; // headers have already been retrieved
        }

        // seek to headers
        $this->handle->seek( $lineNumber );

        // get headers
        $this->headers = $this->getRow();

        $previousLine = $this->line === 0 ? 0 : $this->line - 1;

        // reset to previous set line
        $this->handle->seek( $previousLine );
    }
    
    protected function init()
    {
        if (true === $this->init) {
            return;
        }
        $this->init = true;

        if( $this->headersInFirstRow === true /*&& $this->line === -1*/ ) {
            $this->handle->rewind();

            $this->headers = $this->getRow();
        }
    }

    protected function incrementLine()
    {
        $this->line++;
    }
}
