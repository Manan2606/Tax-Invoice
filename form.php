<?php

require_once 'db.php';

$flag = $_POST['flag'];

if($flag == 'insert')
{
    $invoice_no = $_POST['invoice_no'];
    $invoice_date = $_POST['invoice_date'];
    $customer_name = $_POST['customer_name'];
    $mobile = $_POST['mobile'];
    $customer_gstin = $_POST['customer_gstin'];
    $place_of_supply = $_POST['place_of_supply'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    
    $total_discount = $_POST['total_discount'];
    $total_value = $_POST['total_value'];
    $total_cgst = $_POST['total_cgst'];
    $total_sgst = $_POST['total_sgst'];
    $net_amount = $_POST['net_amount'];
    
    $sql = "INSERT INTO customer_details(`invoice_no`,`customer_name`,`invoice_date`,`mobile`,`customer_gstin`,`place_of_supply`,`address`,`city`,`state`) VALUES (?,?,?,?,?,?,?,?,?)";
    
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "sssssssss", $invoice_no, $customer_name, $invoice_date, $mobile, $customer_gstin, $place_of_supply, $address, $city, $state);
    mysqli_stmt_execute($stmt);
    
    $customer_id = $stmt->insert_id;
    echo $customer_id;
    
    if (isset($_POST['items'])) {
        $items = json_decode($_POST['items'], true); // Decode JSON string into PHP array
    
        if ($items !== null) { // Check if $items is not null
            foreach ($items as $item) {
                // Extract item properties from the $item array
                $serial_no = $item['serial_no'];
                $barcode = $item['barcode'];
                $particulars = $item['particulars'];
                $hsn = $item['hsn'];
                $weight = $item['weight'];
                $qty = $item['qty'];
                $rate = $item['rate'];
                $valueCalculation = $item['valueCalculation'];

                if($valueCalculation == 'qty' . ($serial_no - 1))
                {
                    $value = $qty * $rate;
                }
                else if($valueCalculation == 'weight' . ($serial_no - 1))
                {
                    $value = $weight * $rate;
                }
                
                // Insert the item into the invoice_items table here
                $sql1 = "INSERT INTO invoice_items(`customer_id`, `serial_no`, `barcode`, `particulars`, `hsn`, `weight`, `qty`, `rate`, `value`) VALUES ($customer_id ,?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt1 = mysqli_prepare($con, $sql1);
                mysqli_stmt_bind_param($stmt1, "ssssssss", $serial_no, $barcode, $particulars, $hsn, $weight, $qty, $rate, $value);
                mysqli_stmt_execute($stmt1);
            }
        } else {
            echo "Error: Invalid 'items' data provided in the request.";
        }
    
    } else {
        echo "Error: 'items' data not provided in the request.";
    }
    
    $sql3 = "INSERT INTO customer_bill(`customer_id`,`total_discount`,`total_value`,`total_cgst`,`total_sgst`,`net_amount`) VALUES ($customer_id, ?,?,?,?,?)";
    
    $stmt3 = mysqli_prepare($con, $sql3);
    mysqli_stmt_bind_param($stmt3, "sssss", $total_discount, $total_value, $total_cgst, $total_sgst, $net_amount);
    mysqli_stmt_execute($stmt3);
}
else if($flag == 'select')
{
    $sql2="SELECT MAX(customer_id) AS customer_id FROM customer_details";
    $result2=mysqli_query($con,$sql2);
    if($result2)
    {
        if($result2->num_rows > 0)
        {
            $row = mysqli_fetch_assoc($result2);
            $customer_id = $row['customer_id'];
        }
    }
    print(json_encode(array('status' => true, 'customer_id' => $customer_id)));
}

$con->close();

?>