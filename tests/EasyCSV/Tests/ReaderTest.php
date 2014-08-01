<?php

namespace EasyCSV\Tests;

use EasyCSV\Reader;

class ReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getReaders
     */
    public function testOneAtAtime(Reader $reader)
    {
        while($row = $reader->getRow()) {
            $this->assertTrue(is_array($row));
            $this->assertEquals(3, count($row));
        }
    }

    /**
     * @dataProvider getReaders
     */
    public function testGetAll(Reader $reader)
    {
        $this->assertEquals(5, count($reader->getAll()));
    }

    /**
     * @dataProvider getReaders
     */
    public function testGetHeaders(Reader $reader)
    {
        $this->assertEquals(array("column1", "column2", "column3"), $reader->getHeaders());
    }

    /**
     * @dataProvider getReaders
     */
    public function testAdvanceto(Reader $reader)
    {
        $reader->advanceTo( 3 );

        $this->assertEquals( 3, $reader->getLineNumber() );

        $reader->advanceTo( 0 );

        $row = array
        (
            'column1' => '1column2value',
            'column2' => '1column3value',
            'column3' => '1column4value',
        );

        $actualRow = $reader->getRow();
        $this->assertEquals( $row, $actualRow );

        $reader->advanceTo( 3 );

        $row = array
        (
            'column1' => '3column2value',
            'column2' => '3column3value',
            'column3' => '3column4value',
        );

        $this->assertEquals( $row, $reader->getRow() );
    }

    /**
     * @dataProvider getReadersNoHeadersFirst
     */
    public function testAdvanceToNoHeadersFirst(Reader $reader)
    {
        $row = array( 'column1', 'column2', 'column3' );

        $actualRow = $reader->getRow();
        $this->assertEquals( $row, $actualRow );

        // give it the 'ol one-two-switcharoo
        $reader->advanceTo(3);
        $reader->getRow();
        $reader->advanceTo(0);

        $this->assertEquals( $row, $reader->getRow() );
    }

    /**
     * @dataProvider getReaders
     */
    public function testSetHeaderLine( Reader $reader )
    {
        $headers = array( "column1", "column2", "column3" );

        $this->assertEquals( $headers, $reader->getHeaders() );
        $reader->setHeaderLine(0);

        $this->assertEquals( $headers, $reader->getHeaders() );
    }

    /**
     * @dataProvider getReadersNoHeadersFirst
     */
    public function testSetHeaderLineNoHeadersFirst( Reader $reader  ){
        // set headers
        $reader->setHeaderLine( 1 );
    }
    
    public function getReaders()
    {
        $readerSemiColon = new \EasyCSV\Reader(__DIR__ . '/read_sc.csv');
        $readerSemiColon->setDelimiter(';');
        return array(
            array(new \EasyCSV\Reader(__DIR__ . '/read.csv')),
            array($readerSemiColon),
        );
    }

    public function getReadersNoHeadersFirst()
    {
        $readerSemiColon = new \EasyCSV\Reader(__DIR__ . '/read_sc.csv', 'r+', false );
        $readerSemiColon->setDelimiter(';');
        return array(
            array(new \EasyCSV\Reader(__DIR__ . '/read.csv', 'r+', false )),
            array($readerSemiColon),
        );
    }
}
