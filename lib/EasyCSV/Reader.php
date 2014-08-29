<?php

namespace EasyCSV;

class Reader extends AbstractBase
{
    protected $headersInFirstRow = true;
    protected $headers = false;
    protected $headerLine = false;
    protected $lastLine = false;
    protected $line;
    protected $init;

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
        return $this->handle->key();
    }

    public function getLastLineNumber()
    {
        if( $this->lastLine ) {
            return $this->lastLine;
        }
        $this->handle->seek($this->handle->getSize());
        $lastLine = $this->handle->key();

        $this->handle->rewind();

        return $this->lastLine = $lastLine;
    }

    /**
     * @return array
     */
    public function getCurrentRow()
    {
        return str_getcsv( $this->handle->current(), $this->delimiter, $this->enclosure );
    }

    /**
     * @param $lineNumber zero-based index
     */
    public function advanceTo( $lineNumber )
    {
        if( $this->headerLine > $lineNumber){
            throw new \LogicException("Line Number $lineNumber is before the header line that was set");
        } elseif( $this->headerLine === $lineNumber ){
            throw new \LogicException("Line Number $lineNumber is equal to the header line that was set");
        }

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

        $this->headerLine = $lineNumber;

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
