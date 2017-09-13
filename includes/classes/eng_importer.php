<?php
/**
 * @author Ben Beach <ben@smcm.us>
 * @copyright 2017 Stone Mountain Cabinetry & Millwork
 *
 * Class to handle all importing of Engineer files and information
 * into the system.
 */

class eng_importer {
    /**
     * @var string - contains the file contents itself
     */
    public $file;

    public function __construct($uploadedFile) {
        $this->file = $uploadedFile;
    }

    /**
     * Translates the document into columns inside of an array
     * @return array - contains the columns exploded out
     */
    public function translateDoc() {
        $column = false;

        if(($handle = fopen($this->file, "r")) !== FALSE) {
            // begin the formatting of the file
            $file_contents = file_get_contents($this->file);

            // required to properly format the file, otherwise it comes with a bunch of foreign characters
            $file_contents = mb_convert_encoding($file_contents, 'UTF-8', 'UCS-2LE');

            // explodes the document by new lines
            $lines = explode("\n", $file_contents);

            // for every new line
            foreach($lines as $line) {
                // explode it out further by tabs, thus creating an array of arrays
                $column[] = explode("\t", $line);
            }

            // close the file handler
            fclose($handle);
        }

        return $column;
    }
}