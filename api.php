<?php
require 'vendor/autoload.php';

use \PhpOffice\PhpSpreadsheet\IOFactory;

$csv = new CSV();
$csv;

class CSV
{
    public function __construct()
    {

        $uploadDir = './uploads/'; // Directory where the file will be uploaded
        $fileName = basename($_FILES['file']['name']); // Get the name of the uploaded file
        $targetPath = $uploadDir . $fileName; // Set the target path where the file will be saved

        // Check if the file has been successfully uploaded
        if (move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
            echo 'File uploaded successfully.';
            echo 'File path: ' . $targetPath;
        } else {
            echo 'Error uploading the file.';
        }
        // Load the XLSX file into a PhpSpreadsheet object
        $spreadsheet = IOFactory::load($targetPath);

        // Get all the sheet names in the workbook
        $sheetNames = $spreadsheet->getSheetNames();
        array_shift($sheetNames);
        unset($sheetNames[11]);
        unset($sheetNames[12]);
        unset($sheetNames[13]);
        unset($sheetNames[14]);
        unset($sheetNames[15]);
        // Loop through each sheet and retrieve its data

        $date = date('m.d.y.his');

        // Specify the directory path
        $directory = './downloads/';

        // Create the folder using the current date as the name
        $folderName = $directory . $date;
        mkdir($folderName);

        foreach ($sheetNames as $sheetName) {
            // Get the worksheet object for the current sheet
            $worksheet = $spreadsheet->getSheetByName($sheetName);

            // Get the highest row and column numbers in the worksheet
            $highestRow = $worksheet->getHighestDataRow();
            $highestColumn = $worksheet->getHighestDataColumn();

            // Convert each row of data into an array
            $data = array();
            for ($row = 1; $row <= $highestRow; $row++) {
                $rowData = $worksheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, null, true, false);

                $data[] = $rowData[0];
            }

            // Output the data array for the current sheet
            echo "Sheet name: " . $sheetName . "\n";
            array_shift($data);
            $headers = $data[0];
            foreach ($headers as $key => $h) {
                $headers[$key] = trim($headers[$key]);
            }
            array_shift($data);
            array_shift($data);
            array_shift($data);
            $v = [];
            foreach ($data as $d) {
                array_push($v, array_combine($headers, $d));
            }

            $add_header = ["Product Handle", "Product Title", "VENDOR", "Requires Shipping", "Taxable", "Option1 Name", "Option1 Value", "Option2 Name", "Option2 Value", "Option3 Name", "Option3 Value", "Description", "Price", "Weight Unit", "Grams"];
            array_push($headers, ...$add_header);

            foreach ($v as $key => $value) {
                foreach ($value as $val) {
                    $v[$key]['Product Handle'] = strtolower($sheetName . "-" . str_replace(' ', '-', trim($v[$key]["Model Number"])));
                    $v[$key]['Product Title'] = $sheetName . " " . str_replace(' ', '-', trim($v[$key]["Model Number"]));
                    $v[$key]['VENDOR'] = $sheetName;
                    $v[$key]['Requires Shipping'] = "TRUE";
                    $v[$key]['Taxable'] = "TRUE";
                    $v[$key]['Option1 Name'] = " ";
                    $v[$key]['Option1 Value'] = " ";
                    $v[$key]['Option2 Name'] = " ";
                    $v[$key]['Option2 Value'] = " ";
                    $v[$key]['Option3 Name'] = " ";
                    $v[$key]['Option3 Value'] = " ";
                    $v[$key]['Status'] = $v[$key]["Master Pack Qty"] == "" || $v[$key]["Master Pack Qty"] < 1  ? "draft" : "active";
                    $v[$key]['Description'] = $v[$key]["Product Description Long"] !== "" ? $v[$key]["Product Description Long"] : "";
                    $v[$key]['Price'] = $v[$key]["Status"] !== "active" ? $v[$key]["MAP"] : 0;
                    $v[$key]['Weight Unit'] = "g";
                    $v[$key]['Grams'] = $v[$key][$headers[14]] !== "" ? (float) $v[$key][$headers[14]] * 453.59237 : 0;
                }
            }

            // $filename = "./$folderName/$sheetName.csv";

            // $fp = fopen($filename, 'w');

            // // Write the header row
            // fputcsv($fp, $headers);

            // // Write the data rows
            // foreach ($v as $row) {
            //     fputcsv($fp, $row);
            // }

            // fclose($fp);

            // echo "CSV file '$filename' has been created. \n";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <?php
    foreach ($v as $value) {
        foreach ($value as $vv) {
            echo "<ul><li>" . $vv['Product Title'] . "</li></ul>";
        }
    } ?>
</body>

</html>