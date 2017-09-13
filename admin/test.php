<?php
require ("../includes/header_start.php");

outputPHPErrs();

if(!empty($_REQUEST)) {
    if($_REQUEST['action'] === 'printCSV') {
        $import = new eng_importer($_FILES['attachment']['tmp_name']);

        $results = $import->translateDoc();

        foreach($results as $line) {
            if(count($line) === 1) {
                if(!empty($line)) {
                    echo "<input type='text' value='$line[0]' width='80px'><br />\n";
                }
            } else {
                $numCols = count($line);
                $i = 0;

                for($i = 0; $i < $numCols; $i++) {
                    echo "<input type='text' value='$line[$i]' width='40px'> &nbsp; ";

                    if($i + 1 === $numCols) {
                        echo "<br />\n";
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<body>

<form action="test.php?action=printCSV" method="post" enctype="multipart/form-data">
    Select file to upload:
    <input type="file" name="attachment" id="fileToUpload">
    <input type="submit" value="Upload" name="submit">
</form>

</body>
</html>
